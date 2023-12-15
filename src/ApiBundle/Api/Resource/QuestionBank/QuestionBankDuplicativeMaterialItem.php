<?php

namespace ApiBundle\Api\Resource\QuestionBank;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
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
        $items = $this->getItemService()->findDuplicatedMaterialItems($questionBank['itemBankId'], $request->request->get('categoryId', ''), $request->request->get('material'));
        $categories = $this->getItemCategoryService()->findItemCategoriesByIds(array_column($items, 'category_id'));
        foreach ($items as &$item) {
            $item['category_name'] = empty($categories[$item['category_id']]) ? '' : $categories[$item['category_id']]['name'];
        }

        return $items;
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

    /**
     * @return ItemCategoryService
     */
    private function getItemCategoryService()
    {
        return $this->service('ItemBank:Item:ItemCategoryService');
    }
}
