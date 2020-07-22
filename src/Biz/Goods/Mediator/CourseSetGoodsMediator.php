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
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
        if (empty($existProduct)) {
            throw ProductException::NOTFOUND_PRODUCT();
        }

        $product = $this->getProductService()->updateProduct($existProduct['id'], [
            'title' => $courseSet['title'],
        ]);

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);

        if (empty($existGoods)) {
            throw GoodsException::GOODS_NOT_FOUND();
        }

        $goods = $this->getGoodsService()->updateGoods($existGoods['id'], [
            'title' => $courseSet['title'],
            'subtitle' => $courseSet['subtitle'],
            'summary' => $courseSet['summary'],
            'images' => $courseSet['cover'],
            'orgId' => $courseSet['orgId'],
            'orgCode' => $courseSet['orgCode'],
            'maxRate' => $courseSet['maxRate'],
        ]);

        return [$product, $goods];
    }

    public function onClose($courseSet)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
        if (empty($existProduct)) {
            throw ProductException::NOTFOUND_PRODUCT();
        }

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);

        if (empty($existGoods)) {
            throw GoodsException::GOODS_NOT_FOUND();
        }

        $goods = $this->getGoodsService()->unpublishGoods($existGoods['id']);

        return [$existProduct, $goods];
    }

    public function onPublish($courseSet)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
        if (empty($existProduct)) {
            throw ProductException::NOTFOUND_PRODUCT();
        }

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);
        if (empty($existGoods)) {
            throw GoodsException::GOODS_NOT_FOUND();
        }

        $goods = $this->getGoodsService()->publishGoods($existGoods['id']);

        return [$existProduct, $goods];
    }

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
        $this->getGoodsService()->deleteGoods($existGoods['id']);
    }
}
