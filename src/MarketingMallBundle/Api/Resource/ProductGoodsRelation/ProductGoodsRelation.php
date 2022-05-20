<?php

namespace MarketingMallBundle\Api\Resource\ProductGoodsRelation;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Biz\ProductGoodsRelation\Service\ProductGoodsRelationService;

class ProductGoodsRelation extends BaseResource
{
    public function add(ApiRequest $request)
    {
        $relation = $request->request->all();

        return $this->getProductGoodsRelationService()->createProductGoodsRelation($relation);
    }

    public function remove(ApiRequest $request, $code)
    {
        $relation = $this->getProductGoodsRelationService()->getProductGoodsRelationByGoodsCode($code);
        var_dump($relation);
        exit;

        return $this->getProductGoodsRelationService()->deleteProductGoodsRelation($relation['id']);
    }

    /**
     * @return ProductGoodsRelationService
     */
    private function getProductGoodsRelationService()
    {
        return $this->service('MarketingMallBundle:ProductGoodsRelation:ProductGoodsRelationService');
    }
}
