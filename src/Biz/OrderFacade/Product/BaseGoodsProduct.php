<?php

namespace Biz\OrderFacade\Product;

use Biz\Goods\Service\GoodsService;
use Codeages\Biz\Order\Status\OrderStatusCallback;

abstract class BaseGoodsProduct extends Product implements OrderStatusCallback
{
    public $showTemplate;

    public $targetType;

    public $goods;

    public $goodsSpecs;

    /**
     * 课程展示价格
     *
     * @var float
     */
    public $price;

    public function init(array $params)
    {
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($params['targetId']);
        $goods = $this->getGoodsService()->getGoods($goodsSpecs['goodsId']);

        $this->targetId = $params['targetId'];
        $this->goods = $goods;
        $this->goodsSpecs = $goodsSpecs;

        if (CourseProduct::TYPE == $this->targetType) {
            $this->backUrl = ['routing' => 'goods_show', 'params' => ['id' => $goodsSpecs['goodsId'], 'targetId' => $goodsSpecs['targetId']]];
            $this->title = $goods['title'].'-'.$goodsSpecs['title'];
            $this->successUrl = ['routing' => 'my_course_show', 'params' => ['id' => $goodsSpecs['targetId']]];
        } elseif (ClassroomProduct::TYPE == $this->targetType) {
            $this->backUrl = ['routing' => 'goods_show', 'params' => ['id' => $goodsSpecs['goodsId']]];
            $this->title = $goodsSpecs['title'];
            $this->successUrl = ['routing' => 'classroom_show', 'params' => ['id' => $goodsSpecs['targetId']]];
        }

        $this->productEnable = ('published' === $goods['status'] && 'published' === $goodsSpecs['status']) ? true : false;
        $this->originPrice = $goodsSpecs['price'];
        $this->cover = empty($goodsSpecs['images']) ? $goods['images'] : $goodsSpecs['images'];
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }
}
