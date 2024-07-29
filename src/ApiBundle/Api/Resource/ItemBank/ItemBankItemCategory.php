<?php

namespace ApiBundle\Api\Resource\ItemBank;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;

class ItemBankItemCategory extends AbstractResource
{
    public function add(ApiRequest $request, $bankId)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($bankId);
        if (empty($questionBank)) {
            throw QuestionBankException::NOT_FOUND_BANK();
        }
        if (!$this->getQuestionBankService()->canManageBank($questionBank['id'])) {
            throw UserException::PERMISSION_DENIED();
        }
        $categoryNames = $request->request->get('names');
        $categoryNames = trim($categoryNames);
        $categoryNames = explode("\n", $categoryNames);
        $categoryNames = array_filter($categoryNames);
        $this->getItemCategoryService()->createItemCategories($bankId, 0, $categoryNames);

        return ['ok' => true];
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->service('ItemBank:Item:ItemCategoryService');
    }
}
