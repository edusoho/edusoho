<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\GoodsException;
use Biz\Product\ProductException;

class ClassroomGoodsMediator extends AbstractGoodsMediator
{
    public function onCreate($classroom)
    {
        $product = $this->getProductService()->createProduct([
            'targetType' => 'classroom',
            'targetId' => $classroom['id'],
            'title' => $classroom['title'],
            'owner' => $classroom['creator'],
        ]);

        $goods = $this->getGoodsService()->createGoods([
            'type' => 'classroom',
            'productId' => $product['id'],
            'title' => $classroom['title'],
            'subtitle' => $classroom['subtitle'],
            'creator' => $classroom['creator'],
        ]);

        $this->getClassroomSpecsMediator()->onCreate($classroom);

        return [$product, $goods];
    }

    public function onUpdateNormalData($classroom)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
        if (empty($existProduct)) {
            throw ProductException::NOTFOUND_PRODUCT();
        }

        $product = $this->getProductService()->updateProduct($existProduct['id'], [
            'title' => $classroom['title'],
        ]);

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);

        if (empty($existGoods)) {
            throw GoodsException::GOODS_NOT_FOUND();
        }

        $goods = $this->getGoodsService()->updateGoods($existGoods['id'], [
            'title' => $classroom['title'],
            'subtitle' => $classroom['subtitle'],
            'summary' => $classroom['about'],
            'images' => [
                'large' => $classroom['largePicture'],
                'middle' => $classroom['middlePicture'],
                'small' => $classroom['smallPicture'],
            ],
            'orgId' => $classroom['orgId'],
            'orgCode' => $classroom['orgCode'],
        ]);

        return [$product, $goods];
    }

    public function onClose($classroom)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
        if (empty($existProduct)) {
            throw ProductException::NOTFOUND_PRODUCT();
        }
        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);
        $goods = $this->getGoodsService()->unpublishGoods($existGoods['id']);

        if (empty($existGoods)) {
            throw GoodsException::GOODS_NOT_FOUND();
        }

        return [$existProduct, $goods];
    }

    public function onDelete($classroom)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
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

    public function onPublish($classroom)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
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

    /**
     * @return ClassroomSpecsMediator
     */
    protected function getClassroomSpecsMediator()
    {
        return $this->biz['specs.mediator.classroom'];
    }
}
