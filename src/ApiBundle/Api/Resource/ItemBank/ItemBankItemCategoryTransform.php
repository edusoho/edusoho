<?php

namespace ApiBundle\Api\Resource\ItemBank;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;

class ItemBankItemCategoryTransform extends AbstractResource
{
    public function get(ApiRequest $request, $bankId, $mode)
    {
        if ('tree' === $mode) {
            return $this->getItemCategoryService()->getItemCategoryTree($bankId);
        }
        if ('treeList' === $mode) {
            return $this->getItemCategoryService()->getItemCategoryTreeList($bankId);
        }

        return [];
    }

    /**
     * @return ItemCategoryService
     */
    private function getItemCategoryService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemCategoryService');
    }
}
