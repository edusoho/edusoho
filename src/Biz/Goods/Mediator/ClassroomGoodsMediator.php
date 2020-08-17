<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\GoodsException;
use Biz\Product\ProductException;

/**
 * Class ClassroomGoodsMediator
 * 班级的规格、产品和商品数据都来自于classroom,所以我们将创建流程全部汇集到goodsMediator入口，代理调用规格的对应操作
 */
class ClassroomGoodsMediator extends AbstractGoodsMediator
{
    /**
     * @var string[]
     */
    public $normalFields = [
        'title',
        'subtitle',
        'about',
        'orgId',
        'orgCode',
        'categoryId',
        'smallPicture',
        'middlePicture',
        'largePicture',
        'price',
        'buyable',
        'showable',
        'expiryMode',
        'expiryValue',
        'service',
    ];

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
        list($product, $goods) = $this->getProductAndGoods($classroom);

        $product = $this->getProductService()->updateProduct($product['id'], [
            'title' => $classroom['title'],
        ]);

        $goods = $this->getGoodsService()->updateGoods($goods['id'], [
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

        $this->getClassroomSpecsMediator()->onUpdateNormalData($classroom);

        return [$product, $goods];
    }

    public function onPublish($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goods = $this->getGoodsService()->publishGoods($goods['id']);
        $this->getClassroomSpecsMediator()->onPublish($classroom);

        return [$product, $goods];
    }

    public function onClose($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goods = $this->getGoodsService()->unpublishGoods($goods['id']);
        $this->getClassroomSpecsMediator()->onClose($classroom);

        return [$product, $goods];
    }

    public function onDelete($classroom)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
        if (empty($existProduct)) {
            return;
        }
        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);
        if (empty($existGoods)) {
            return;
        }
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($existGoods['id'], $classroom['id']);

        $this->getGoodsService()->deleteGoodsSpecs($goodsSpecs['id']);
        $this->getGoodsService()->deleteGoods($existGoods['id']);
        $this->getProductService()->deleteProduct($existProduct['id']);
    }

    public function onRecommended($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goods = $this->getGoodsService()->recommendGoods($goods['id'], $classroom['recommendedSeq']);

        return [$product, $goods];
    }

    public function onCancelRecommended($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goods = $this->getGoodsService()->cancelRecommendGoods($goods['id']);

        return [$product, $goods];
    }

    public function onMaxRateChange($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goods = $this->getGoodsService()->changeGoodsMaxRate($goods['id'], $classroom['maxRate']);

        return [$product, $goods];
    }

    public function onSortGoodsSpecs($classroom)
    {
        return null;
    }

    /**
     * @return ClassroomSpecsMediator
     */
    protected function getClassroomSpecsMediator()
    {
        return $this->biz['specs.mediator.classroom'];
    }

    protected function getProductAndGoods($classroom)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
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
