<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
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
        $this->assertEquals($product->successUrl, ['classroom_show', ['id' => $goodsSpecs['targetId']]]);
        $this->assertEquals($product->title, $goodsSpecs['title']);
        $this->assertEquals($product->originPrice, $goodsSpecs['price']);
        $this->assertEquals([], $goodsSpecs['images']);
    }

    protected function createPublishedClassroomGoodsAndGoodsSpecs($goods = [], $goodsSpecs = [], $classroom = [])
    {
        $classroom = array_merge(['title' => 'test classroom title', 'subtitle' => 'test classroom subtitle'], $classroom);
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $course = $this->createCourse('Test Course 1');
        $courseIds = [$course['id']];
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
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

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function mockCourse($title = 'Test Course 1')
    {
        return [
            'title' => $title,
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        ];
    }

    private function createCourse($title)
    {
        $courseSet = [
            'title' => '新课程开始！',
            'type' => 'normal',
        ];

        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);
        $course = $this->mockCourse($title);
        $course['courseSetId'] = $courseSet['id'];

        return $this->getCourseService()->createCourse($course);
    }
}
