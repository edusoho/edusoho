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

    public function checkMallGoods($productId, $type)
    {
        $relation = $this->getProductMallGoodsRelationByProductTypeAndProductId($type, $productId);
        if ($relation) {
            $client = new MarketingMallClient($this->biz);
//            file_put_contents('/tmp/test',\GuzzleHttp\json_encode($client->checkGoodsIsPublishByCode($relation['goodsCode'])));
            if ($client->checkGoodsIsPublishByCode($relation['goodsCode'])['success']) {
                throw $this->createServiceException('该产品已在营销商城中上架售卖，请将对应商品下架后再进行删除操作');
            }
            return 1;
        }
        return 0;
    }

    public function deleteMallGoodsByCode($code)
    {
        try {
            $relation = $this->getProductMallGoodsRelationByGoodsCode($code);
            if ($relation) {
                $this->deleteProductMallGoodsRelation($relation['id']);
                $client = new MarketingMallClient($this->biz);
                file_put_contents('/tmp/test', \GuzzleHttp\json_encode($client->deleteGoodsBycode($relation['goodsCode'])));
                $result = $client->deleteGoodsBycode('eq110');
                if (!$result['success']) {
                    throw $this->createServiceException('删除营销商城商品失败，请重试！');
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
        return true;
    }

    /**
     * @return ProductMallGoodsRelationDao
     */
    protected function getProductMallGoodsRelationDao()
    {
        return $this->createDao('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationDao');
    }
}
