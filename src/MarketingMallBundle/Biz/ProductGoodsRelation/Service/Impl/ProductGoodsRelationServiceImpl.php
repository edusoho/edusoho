<?php

namespace MarketingMallBundle\Biz\ProductGoodsRelation\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use MarketingMallBundle\Biz\ProductGoodsRelation\Dao\ProductGoodsRelationDao;
use MarketingMallBundle\Biz\ProductGoodsRelation\Service\ProductGoodsRelationService;

class ProductGoodsRelationServiceImpl extends BaseService implements ProductGoodsRelationService
{
    public function getProductGoodsRelationByGoodsCode($code)
    {
        return $this->getProductGoodsRelationDao()->getByGoodsCode($code);
    }

    public function createProductGoodsRelation($relation)
    {
        if (!ArrayToolkit::requireds($relation, ['productType', 'productId', 'goodsCode'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $relation = ArrayToolkit::parts($relation, ['productType', 'productId', 'goodsCode']);

        return $this->getProductGoodsRelationDao()->create($relation);
    }

    public function deleteProductGoodsRelation($id)
    {
        return $this->getProductGoodsRelationDao()->delete($id);
    }

    public function getProductGoodsRelationByProductTypeAndProductId($productType, $productId)
    {
        return $this->getProductGoodsRelationDao()->getByTargetIdAndType($productType, $productId);
    }

    /**
     * @return ProductGoodsRelationDao
     */
    protected function getProductGoodsRelationDao()
    {
        return $this->createDao('MarketingMallBundle:ProductGoodsRelation:ProductGoodsRelationDao');
    }
}
