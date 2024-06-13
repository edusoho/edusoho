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
        $methodName = 'getCountItemBy'.ucfirst($type);

        return $this->$methodName($conditions);
    }

    public function getCountItemByDifficulty($conditions)
    {
        if (!isset($conditions['bank_id'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->getItemService()->getItemCountGroupByDifficulty($conditions);
    }

    public function getCountItemByQuestionType($conditions)
    {
        if (!isset($conditions['bank_id'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->getItemService()->getItemCountGroupByTypes($conditions);
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }
}
