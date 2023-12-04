<?php

namespace ApiBundle\Api\Resource\Item;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class Item extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $item = $this->getItemService()->getItemWithQuestions($id, true);
        if ($item['category_id']) {
            $category = $this->getItemCategoryService()->getItemCategory($item['category_id']);
            $item['category_name'] = $category['name'];
        }

        return $item;
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
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
