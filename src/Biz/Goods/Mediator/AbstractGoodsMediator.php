<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;

abstract class AbstractGoodsMediator
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Container $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $target
     *
     * @return mixed
     *               创建触发
     */
    abstract public function onCreate($target);

    /**
     * @param $target
     *
     * @return mixed
     *               基本信息更新
     */
    abstract public function onUpdateNormalData($target);

    /**
     * @param $target
     *
     * @return mixed
     *               取消发布
     */
    abstract public function onClose($target);

    /**
     * @param $target
     *
     * @return mixed
     *               发布
     */
    abstract public function onPublish($target);

    /**
     * @param $target
     *
     * @return mixed
     *               删除
     */
    abstract public function onDelete($target);

    /**
     * @param $target
     *
     * @return mixed
     *               推荐商品
     */
    abstract public function onRecommended($target);

    /**
     * @param $target
     *
     * @return mixed
     *               取消推荐
     */
    abstract public function onCancelRecommended($target);

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
