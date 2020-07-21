<?php

namespace Biz\Goods;

use Biz\Goods\Mediator\ClassroomGoodsMediator;
use Biz\Goods\Mediator\CourseSetGoodsMediator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GoodsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        /*
         * @return CourseSetGoodsMediator
         *Course Mediator
         */
        $biz['goods.mediator.course'] = static function () use ($biz) {
            return new CourseSetGoodsMediator($biz);
        };

        /*
         * @return ClassroomGoodsMediator
         * Classroom Mediator
         */
        $biz['goods.mediator.classroom'] = static function () use ($biz) {
            return new ClassroomGoodsMediator($biz);
        };
    }
}
