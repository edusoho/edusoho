<?php

namespace MarketingMallBundle\Api\Resource\ProductMallGoodsRelation;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;

class ProductMallGoodsRelation extends BaseResource
{
    public function add(ApiRequest $request)
    {
        $relation = $request->request->all();
        $result = $this->getProductMallGoodsRelationService()->createProductMallGoodsRelation($relation);

        return !empty($result) ? ['success' => true] : ['success' => false];
    }

    public function remove(ApiRequest $request, $code)
    {
        $relation = $this->getProductMallGoodsRelationService()->getProductMallGoodsRelationByGoodsCode($code);
        if (empty($relation)) {
            return ['success' => true];
        }

        return $this->getProductMallGoodsRelationService()->deleteProductMallGoodsRelation($relation['id']) ? ['success' => true] : ['success' => false];
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    private function getProductMallGoodsRelationService()
    {
        return $this->service('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
