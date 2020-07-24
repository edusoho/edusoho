<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;

abstract class AbstractSpecsMediator
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Container $biz)
    {
        $this->biz = $biz;
    }

    abstract public function onCreate($target);

    abstract public function onUpdateNormalData($target);

    abstract public function onPriceUpdate($target);

    abstract public function onPublish($target);

    abstract public function onClose($target);

    abstract public function onDelete($target);

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->biz->service('Product:ProductService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }
}
