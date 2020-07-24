<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\Classroom\Service\ClassroomService;
use Biz\Goods\Service\GoodsService;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\Product\Service\ProductService;

class ClassroomProductTest extends BaseTestCase
{
    public function testValidate()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Classroom:ClassroomService', [
            ['functionName' => 'getClassroom', 'returnValue' => ['buyable' => true]],
            ['functionName' => 'canJoinClassroom', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);
        $this->assertEquals(null, $classroomProduct->validate());
    }

    /**
     * @expectedException  \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateOnErrorWhenClassroomUnPurchasable()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Classroom:ClassroomService', [
            ['functionName' => 'getClassroom', 'returnValue' => ['buyable' => 0]],
            ['functionName' => 'canJoinClassroom', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);

        $classroomProduct->validate();
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateWithError()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'canJoinCourse', 'returnValue' => ['code' => 'error', 'msg' => 'wrong']],
        ]);

        $classroomProduct->validate();
    }

    public function testInit()
    {
        list($goods, $goodsSpecs, $classroom) = $this->createPublishedClassroomGoodsAndGoodsSpecs();

        $product = new ClassroomProduct();
        $product->setBiz($this->getBiz());
        $product->init(['targetId' => $goodsSpecs['id']]);

        $this->assertEquals($product->targetId, $goodsSpecs['id']);
        $this->assertEquals($product->goods, $goods);
        $this->assertEquals($product->goodsSpecs, $goodsSpecs);
        $this->assertEquals($product->backUrl, ['routing' => 'goods_show', 'params' => ['id' => $goodsSpecs['goodsId']]]);
        $this->assertEquals($product->successUrl, ['routing' => 'classroom_show', 'params' => ['id' => $goodsSpecs['targetId']]]);
        $this->assertEquals($product->title, $goodsSpecs['title']);
        $this->assertEquals($product->originPrice, $goodsSpecs['price']);
        $this->assertTrue($product->productEnable);
        $this->assertEquals($product->cover, $goodsSpecs['images']);
    }

    protected function createPublishedClassroomGoodsAndGoodsSpecs($goods = [], $goodsSpecs = [], $classroom = [])
    {
        $classroom = array_merge(['title' => 'test classroom title'], $classroom);
        $classroom = $this->getClassroomService()->addClassroom($classroom);

        $this->getClassroomService()->publishClassroom($classroom['id']);

        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);

        $product = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');

        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($product['id'], $classroom['id']);
        $goods = $this->getGoodsService()->getGoods($goodsSpecs['goodsId']);

        return [$goods, $goodsSpecs, $classroom];
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
