<?php

namespace Tests\Unit\Goods\Mediator;

use Biz\BaseTestCase;
use Biz\Classroom\Service\ClassroomService;
use Biz\Goods\Mediator\ClassroomGoodsMediator;
use Biz\Goods\Service\GoodsService;

class ClassroomGoodsMediatorTest extends BaseTestCase
{
    public function testOnCreate()
    {
    }

    /**
     * @return ClassroomGoodsMediator
     */
    protected function getClassroomGoodsMediator()
    {
        return $this->biz['goods.mediator.classroom'];
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
