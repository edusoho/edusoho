<?php

namespace ApiBundle\Api\Resource\Item;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class ItemCount extends AbstractResource
{
    public function search(ApiRequest $request, $type)
    {
        $conditions = $request->query->all();
        if (!isset($conditions['bank_id'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $methodName = 'countItemBy' . ucfirst($type);

        return $this->$methodName($conditions);
    }

    private function countItemByDifficulty($conditions)
    {
        return $this->getItemService()->getItemCountGroupByDifficulty($conditions);
    }

    private function countItemByQuestionType($conditions)
    {
        return $this->getItemService()->getItemCountGroupByTypes($conditions);
    }

    private function countItemByCategoryIdAndType($conditions)
    {
        return $this->getItemService()->countItemGroupByCategoryIdAndType($conditions);
    }

    private function countItemByCategoryId($conditions)
    {
        return $this->getItemService()->countItemGroupByCategoryId($conditions);
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }
}
