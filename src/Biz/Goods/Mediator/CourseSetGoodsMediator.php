<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\GoodsException;
use Biz\Product\ProductException;

class CourseSetGoodsMediator extends AbstractGoodsMediator
{
    /**
     * @var string[] 标记哪些字段可以通用更新
     */
    public $normalFields = [
        'title',
        'subtitle',
        'summary',
        'orgId',
        'orgCode',
        'maxRate',
    ];

    public function onCreate($courseSet)
    {
        $product = $this->getProductService()->createProduct([
            'targetType' => 'course',
            'targetId' => $courseSet['id'],
            'title' => $courseSet['title'],
            'owner' => $courseSet['creator'],
        ]);

        $goods = $this->getGoodsService()->createGoods([
            'type' => 'course',
            'productId' => $product['id'],
            'title' => $courseSet['title'],
            'subtitle' => $courseSet['subtitle'],
            'creator' => $courseSet['creator'],
        ]);

        return [$product, $goods];
    }

    public function onUpdateNormalData($courseSet)
    {
        list($product, $goods) = $this->getProductAndGoods($courseSet);

        $product = $this->getProductService()->updateProduct($product['id'], [
            'title' => $courseSet['title'],
        ]);

        $goods = $this->getGoodsService()->updateGoods($goods['id'], [
            'title' => $courseSet['title'],
            'subtitle' => $courseSet['subtitle'],
            'summary' => $courseSet['summary'],
            'images' => $courseSet['cover'],
            'orgId' => $courseSet['orgId'],
            'orgCode' => $courseSet['orgCode'],
            'categoryId' => $courseSet['categoryId'],
            'maxRate' => $courseSet['maxRate'],
        ]);

        return [$product, $goods];
    }

    public function onClose($courseSet)
    {
        list($product, $goods) = $this->getProductAndGoods($courseSet);

        $goods = $this->getGoodsService()->unpublishGoods($goods['id']);

        return [$product, $goods];
    }

    public function onPublish($courseSet)
    {
        list($product, $goods) = $this->getProductAndGoods($courseSet);

        $goods = $this->getGoodsService()->publishGoods($goods['id']);

        return [$product, $goods];
    }

    /**
     * @param $courseSet
     * 删除课程的同时触发商品的删除，同时删除规格
     */
    public function onDelete($courseSet)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
        if (empty($existProduct)) {
            return;
        }
        $this->getProductService()->deleteProduct($existProduct['id']);

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);
        if (empty($existGoods)) {
            return;
        }
        $goodsSpecs = $this->getGoodsService()->findGoodsSpecsByGoodsId($existGoods['id']);
        foreach ($goodsSpecs as $goodsSpec) {
            $this->getGoodsService()->deleteGoodsSpecs($goodsSpec['id']);
        }

        $this->getGoodsService()->deleteGoods($existGoods['id']);
    }

    public function onRecommended($courseSet)
    {
    }

    public function onCancelRecommended($courseSet)
    {
    }

    public function onMaxRateChange($courseSet)
    {
        // TODO: Implement onMaxRateChange() method.
    }

    protected function getProductAndGoods($courseSet)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
        if (empty($existProduct)) {
            throw ProductException::NOTFOUND_PRODUCT();
        }

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);
        if (empty($existGoods)) {
            throw GoodsException::GOODS_NOT_FOUND();
        }

        return [$existProduct, $existGoods];
    }
}
