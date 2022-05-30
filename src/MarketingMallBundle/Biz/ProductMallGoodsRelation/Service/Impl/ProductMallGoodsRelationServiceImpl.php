<?php

namespace MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Dao\ProductMallGoodsRelationDao;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use MarketingMallBundle\Client\MarketingMallClient;

class ProductMallGoodsRelationServiceImpl extends BaseService implements ProductMallGoodsRelationService
{
    public function getProductMallGoodsRelationByGoodsCode($code)
    {
        return $this->getProductMallGoodsRelationDao()->getByGoodsCode($code);
    }

    public function createProductMallGoodsRelation($relation)
    {
        if (!ArrayToolkit::requireds($relation, ['productType', 'productId', 'goodsCode'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $relation = ArrayToolkit::parts($relation, ['productType', 'productId', 'goodsCode']);

        return $this->getProductMallGoodsRelationDao()->create($relation);
    }

    public function deleteProductMallGoodsRelation($id)
    {
        return $this->getProductMallGoodsRelationDao()->delete($id);
    }

    public function getProductMallGoodsRelationByProductTypeAndProductId($productType, $productId)
    {
        return $this->getProductMallGoodsRelationDao()->getByProductTypeAndProductId($productType, $productId);
    }

    public function findProductMallGoodsRelationsByProductType($productType)
    {
        return $this->getProductMallGoodsRelationDao()->findByProductType($productType);
    }

    public function findProductMallGoodsRelationsByProductIdsProductType($productIds, $productType)
    {
        return $this->getProductMallGoodsRelationDao()->search(['productIds' => $productIds, 'type' => $productType], [], 0, PHP_INT_MAX);
    }

    public function checkMallGoods(array $productIds, $type)
    {
        $relations = $this->findProductMallGoodsRelationsByProductIdsProductType($productIds, $type);
        if ($relations) {
            $client = new MarketingMallClient($this->biz);
            $result = $client->checkGoodsIsPublishByCodes(ArrayToolkit::column($relations,'goodsCode'));
            if (in_array(true, $result)) {
                throw $this->createServiceException('该产品已在营销商城中上架售卖，请将对应商品下架后再进行删除操作');
            }
            return true;
        }
        return false;
    }

    /**
     * @return ProductMallGoodsRelationDao
     */
    protected function getProductMallGoodsRelationDao()
    {
        return $this->createDao('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationDao');
    }
}
