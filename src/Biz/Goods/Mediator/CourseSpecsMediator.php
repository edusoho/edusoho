<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\GoodsException;

class CourseSpecsMediator extends AbstractSpecsMediator
{
    public function onCreate($course)
    {
        list($product, $goods) = $this->getProductAndGoods($course);

        if (empty($goods)) {
            return [];
        }

        $goodsSpecs = $this->getGoodsService()->createGoodsSpecs([
            'goodsId' => $goods['id'],
            'targetId' => $course['id'],
            'title' => $course['title'],
            'seq' => $course['seq'],
            'usageMode' => $course['expiryMode'],
            'usageDays' => $course['expiryDays'] ?: 0,
            'usageStartTime' => $course['expiryStartDate'] ?: 0,
            'usageEndTime' => $course['expiryEndDate'] ?: 0,
        ]);

        return $goodsSpecs;
    }

    public function onUpdateNormalData($course)
    {
        list($product, $goods) = $this->getProductAndGoods($course);

        if (empty($goods)) {
            return [];
        }

        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $course['id']);
        $goodsSpecs = $this->getGoodsService()->updateGoodsSpecs($goodsSpecs['id'], [
            'title' => $course['title'],
            'images' => $goods['images'],
            'seq' => $course['seq'],
            'price' => $course['originPrice'],
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

        if (empty($goods)) {
            return [];
        }

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
            return [[], []];
        }

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);
        if (empty($existGoods)) {
            throw GoodsException::GOODS_NOT_FOUND();
        }

        return [$existProduct, $existGoods];
    }
}
