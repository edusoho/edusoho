<?php

namespace ApiBundle\Api\Resource\QuestionBank;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class QuestionBankDuplicativeMaterialItem extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw UserException::PERMISSION_DENIED();
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            throw QuestionBankException::NOT_FOUND_BANK();
        }

        return $this->getItemService()->findDuplicatedMaterialItems($questionBank['itemBankId'], $request->request->get('material'));
    }

    /**
     * @return QuestionBankService
     */
    private function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ItemService
     */
    private function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }
}
