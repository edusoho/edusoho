<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Codeages\Biz\Framework\Context\Biz;

class AbstractGoodsMediator
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function onCreate();

    abstract public function onUpdateNormalData();

    abstract public function onPriceUpdate();

    abstract public function onClose();

    abstract public function onPublish();

    abstract public function onDelete();

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->biz->service('Product:ProductService');
    }
}
