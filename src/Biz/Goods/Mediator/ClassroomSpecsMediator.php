<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\GoodsException;
use Biz\Product\ProductException;

class ClassroomSpecsMediator extends AbstractSpecsMediator
{
    /**
     * @param $classroom
     *
     * @return mixed
     */
    public function onCreate($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);

        return $this->getGoodsService()->createGoodsSpecs([
            'goodsId' => $goods['id'],
            'targetId' => $classroom['id'],
            'title' => $classroom['title'],
            'seq' => 1,
            'usageMode' => $classroom['expiryMode'],
        ]);
    }

    public function onUpdateNormalData($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);

        return $this->getGoodsService()->updateGoodsSpecs($goodsSpecs['id'], [
            'title' => $classroom['title'],
            'images' => $goods['images'],
            'price' => $classroom['price'],
            'showable' => $classroom['showable'],
            'buyable' => $classroom['buyable'],
            'buyableStartTime' => 0,
            'buyableEndTime' => 0,
            'usageMode' => 'date' === $classroom['expiryMode'] ? 'end_date' : $classroom['expiryMode'],
            'usageDays' => 'days' === $classroom['expiryMode'] ? $classroom['expiryValue'] : 0,
            'usageStartTime' => 0,
            'usageEndTime' => 'date' === $classroom['expiryMode'] ? $classroom['expiryValue'] : 0,
            'services' => $classroom['service'],
        ]);
    }

    public function onPublish($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);

        return $this->getGoodsService()->publishGoodsSpecs($goodsSpecs['id']);
    }

    /**
     * @param $classroom
     * 班级无专门的价格更新入口
     */
    public function onPriceUpdate($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);

        return $this->getGoodsService()->changeGoodsSpecsPrice($goodsSpecs, $classroom['price']);
    }

    public function onClose($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);

        return $this->getGoodsService()->unpublishGoodsSpecs($goodsSpecs['id']);
    }

    /**
     * @param $classroom
     * 班级目前不能删除
     */
    public function onDelete($classroom)
    {
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);
        $this->getGoodsService()->deleteGoodsSpecs($goodsSpecs['id']);
    }

    /**
     * @param $classroom
     *
     * @return array
     */
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
