<?php

namespace Tests\Unit\Goods\Mediator;

use Biz\BaseTestCase;
use Biz\Goods\Mediator\ClassroomGoodsMediator;
use Biz\Goods\Mediator\ClassroomSpecsMediator;
use Biz\Goods\Service\GoodsService;

class ClassroomSpecsMediatorTest extends BaseTestCase
{
    public function testOnCreate()
    {
        $classroom = $this->mockClassroom();
        list($product, $goods) = $this->getClassroomGoodsMediator()->onCreate($classroom);
        $specs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);
        self::assertNotEmpty($specs);
        self::assertEquals($classroom['title'], $specs['title']);
    }

    public function testOnUpdateNormalData()
    {
        $classroom = $this->mockClassroom();
        list($product, $goods) = $this->getClassroomGoodsMediator()->onCreate($classroom);
        $updatedClassroom = $this->mockClassroom(['price' => 10.00]);
        $specs = $this->getClassroomSpecsMediator()->onUpdateNormalData($updatedClassroom);
        self::assertEquals(10.00, $specs['price']);
    }

    public function testOnPublish()
    {
        $classroom = $this->mockClassroom();
        list($product, $goods) = $this->getClassroomGoodsMediator()->onCreate($classroom);
        $specs = $this->getClassroomSpecsMediator()->onPublish($classroom);
        self::assertEquals('published', $specs['status']);
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }

    /**
     * @return ClassroomGoodsMediator
     */
    protected function getClassroomGoodsMediator()
    {
        return $this->biz['goods.mediator.classroom'];
    }

    /**
     * @return ClassroomSpecsMediator
     */
    protected function getClassroomSpecsMediator()
    {
        return $this->biz['specs.mediator.classroom'];
    }

    protected function mockClassroom($customFields = [])
    {
        return array_merge([
            'id' => 1,
            'title' => '测试班级商品',
            'subtitle' => '测试班级商品副标题',
            'about' => '测试班级商品简介',
            'largePicture' => 'public://course/2018/07-25/150507367180941893.jpg',
            'smallPicture' => 'public://course/2018/07-25/150507367180941893.jpg',
            'middlePicture' => 'public://course/2018/07-25/150507367180941893.jpg',
            'orgCode' => '1.1',
            'orgId' => 1,
            'price' => 0.00,
            'showable' => 1,
            'buyable' => 1,
            'expiryMode' => 'forever',
            'service' => [],
            'creator' => 1,
        ], $customFields);
    }
}
