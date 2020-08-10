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

    public $originalTargetId;

    /**
     * 课程展示价格
     *
     * @var float
     */
    public $price;

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }
}
