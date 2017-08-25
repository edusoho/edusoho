<?php

namespace Biz\OrderFacade\Product;

use Codeages\Biz\Framework\Context\BizAware;

abstract class Product extends BizAware
{
    /**
     * 商品ID
     *
     * @var int
     */
    public $targetId;

    /**
     * 商品类型
     *
     * @var string
     */
    public $targetType;

    /**
     * 商品名称
     *
     * @var string
     */
    public $title;

    /**
     * 商品价格
     *
     * @var float
     */
    public $price;

    /**
     * 应付价格
     *
     * @var float
     */
    public $payablePrice;

    /**
     * 可使用的折扣
     *
     * @var array
     */
    public $availableDeducts = array();

    /**
     * 使用到的折扣
     *
     * @var array
     */
    public $pickedDeducts = array();

    abstract public function init(array $params);

    abstract public function validate();
}
