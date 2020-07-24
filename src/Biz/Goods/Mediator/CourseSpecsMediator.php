<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\GoodsException;
use Biz\Product\ProductException;

class CourseSpecsMediator extends AbstractSpecsMediator
{
    public function onCreate($course)
    {
        list($product, $goods) = $this->getProductAndGoods($course);
        $goodsSpecs = $this->getGoodsService()->createGoodsSpecs([
            'goodsId' => $goods['id'],
            'targetId' => $course['id'],
            'title' => empty($course['title']) ? $course['courseSetTitle'] : $course['title'],
            'seq' => $course['seq'],
            'usageMode' => $course['expiryMode'],
        ]);

        return $goodsSpecs;
    }

    public function onUpdateNormalData($course)
    {
        list($product, $goods) = $this->getProductAndGoods($course);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $course['id']);
        $goodsSpecs = $this->getGoodsService()->updateGoodsSpecs($goodsSpecs['id'], [
            'title' => empty($course['title']) ? $course['courseSetTitle'] : $course['title'],
            'images' => $goods['images'],
            'seq' => $course['seq'],
            'price' => $course['price'],
            'coinPrice' => $course['coinPrice'],
            'usageMode' => $course['expiryMode'],
            'usageDays' => $course['expiryDays'] ?: 0,
            'usageStartTime' => $course['expiryStartDate'] ?: 0,
            'usageEndTime' => $course['expiryEndDate'] ?: 0,
            'buyable' => $course['buyable'],
            'buyableStartTime' => 0,
            'buyableEndTime' => $course['buyExpiryTime'] ?: 0,
            'maxJoinNum' => $course['maxStudentNum'],
            'services' => $course['services'],
        ]);

        return $goodsSpecs;
    }

    public function onPublish($course)
    {
        list($product, $goods) = $this->getProductAndGoods($course);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $course['id']);
        $goodsSpecs = $this->getGoodsService()->publishGoodsSpecs($goodsSpecs['id']);

        return $goodsSpecs;
    }

    public function onPriceUpdate($course)
    {
        // TODO: Implement onPriceUpdate() method.
    }

    public function onClose($course)
    {
        list($product, $goods) = $this->getProductAndGoods($course);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $course['id']);
        $goodsSpecs = $this->getGoodsService()->unpublishGoodsSpecs($goodsSpecs['id']);

        return $goodsSpecs;
    }

    public function onDelete($course)
    {
        list($product, $goods) = $this->getProductAndGoods($course);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $course['id']);
        $this->getGoodsService()->deleteGoodsSpecs($goodsSpecs['id']);
    }

    protected function getProductAndGoods($course)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($course['courseSetId'], 'course');
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
