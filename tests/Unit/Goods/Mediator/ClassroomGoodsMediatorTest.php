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
        $classroom = $this->mockClassroom();
        list($product, $goods) = $this->getClassroomGoodsMediator()->onCreate($classroom);
        self::assertEquals($classroom['title'], $product['title']);
        self::assertEquals($classroom['id'], $product['targetId']);
        self::assertEquals($classroom['title'], $goods['title']);
        self::assertEquals($classroom['subtitle'], $goods['subtitle']);
        self::assertEquals('classroom', $goods['type']);
    }

    public function testOnNormalDataUpdate()
    {
        $classroom = $this->mockClassroom();
        list($product, $goods) = $this->getClassroomGoodsMediator()->onCreate($classroom);
        self::assertEquals($classroom['title'], $goods['title']);
        $updatedClassroom = $this->mockClassroom(['title' => '测试班级商品-改']);
        list($updatedProduct, $updatedGoods) = $this->getClassroomGoodsMediator()->onUpdateNormalData($updatedClassroom);
        self::assertEquals($updatedClassroom['title'], $updatedGoods['title']);
    }

    public function testOnPublish()
    {
    }

    public function mockClassroom($customFields = [])
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
            'creator' => 1,
        ], $customFields);
    }

    protected function createSpecs($customFields = [])
    {
        return $this->getGoodsService()->createGoodsSpecs(array_merge([
            'goodsId' => 1,
            'targetId' => 1,
            'title' => '测试班级规格',
            'seq' => 1,
            'usageMode' => 'forever',
        ], $customFields));
    }

    protected function createGoods($customFields = [])
    {
        return $this->getGoodsService()->createGoods(array_merge([
            'targetType' => 'classroom',
            'targetId' => 1,
            'title' => '测试班级商品',
            'owner' => 1,
        ], $customFields));
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
