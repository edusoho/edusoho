<?php

namespace ApiBundle\Api\Resource\ScrmGood;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Goods\Service\GoodsService;

class ScrmGood extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $request->query->get('id');
        $this->getGoodsService()->countGoods([]);

        return $this->getGoodsService()->searchGoods([], [], 0, 1000);
    }

    protected function filterConditions($conditions)
    {
        $conditions['targetType'] = 'course';

        return $conditions;
    }

    /**
     * @return GoodsService
     */
    public function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }
}
