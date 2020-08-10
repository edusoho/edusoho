<?php

namespace Biz\Goods;

use Biz\Goods\Mediator\ClassroomGoodsMediator;
use Biz\Goods\Mediator\ClassroomSpecsMediator;
use Biz\Goods\Mediator\CourseSetGoodsMediator;
use Biz\Goods\Mediator\CourseSpecsMediator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GoodsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        /*
         * @return CourseSetGoodsMediator
         */
        $biz['goods.mediator.course_set'] = static function () use ($biz) {
            return new CourseSetGoodsMediator($biz);
        };

        /*
         * @return ClassroomGoodsMediator
         */
        $biz['goods.mediator.classroom'] = static function () use ($biz) {
            return new ClassroomGoodsMediator($biz);
        };

        /*
         * @return CourseSpecsMediator
         */
        $biz['specs.mediator.course'] = static function () use ($biz) {
            return new CourseSpecsMediator($biz);
        };

        /*
         * @return ClassroomSpecsMediator
         */
        $biz['specs.mediator.classroom'] = static function () use ($biz) {
            return new ClassroomSpecsMediator($biz);
        };

        /*
         * @return GoodsEntityFactory
         */
        $biz['goods.entity.factory'] = static function () use ($biz) {
            return new GoodsEntityFactory($biz);
        };
    }
}
