<?php

namespace ApiBundle\Api\Resource\ScrmGood;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Goods\GoodsException;
use Biz\Goods\Service\GoodsService;

class ScrmGoodSpec extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $goodsId, $id)
    {
        $spec = $this->getGoodsService()->getGoodsSpecs($id);
        if (empty($spec) || $spec['goodsId'] != $goodsId || 'published' !== $spec['status']) {
            throw GoodsException::SPECS_NOT_FOUND(); //4047202
        }

        return $spec;
    }

    /**
     * @return GoodsService
     */
    public function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }
}
