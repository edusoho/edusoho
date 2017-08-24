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
    public $id;

    /**
     * 商品类型
     *
     * @var string
     */
    public $type;

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
     * 商品营销属性
     *
     * @var array
     */
    public $marketing = array();

    /**
     * 使用到的营销
     *
     * @var array
     */
    public $useMarketing = array();

    abstract public function init(array $params);

    abstract public function validate();
}
