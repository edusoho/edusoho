<?php

namespace Biz\Goods\Mediator;

use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;

/**
 * Class AbstractGoodsMediator
 * 关于为什么课程和班级的更新不是通过事件，而是通过中介者的模式去实现:
 * 是因为，商品属于主进程、关键路径，使用事件后，导致代码分散，可读性以及可扩展性急剧下降，使用中介模式既可以解耦课程和商品的关系，代码可读性也提高了.
 **/
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
     * @param $target
     *
     * @return mixed
     *               最大折扣百分比
     */
    abstract public function onMaxRateChange($target);

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
