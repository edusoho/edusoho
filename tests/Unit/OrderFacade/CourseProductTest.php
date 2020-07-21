<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Service\GoodsService;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\Product\Service\ProductService;

class CourseProductTest extends BaseTestCase
{
    public function testValidate()
    {
        $courseProduct = new CourseProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => ['buyable' => true]],
            ['functionName' => 'canJoinCourse', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);

        $this->assertEquals(null, $courseProduct->validate());
    }

    /**
     * @expectedException  \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateOnErrorWhenCourseUnPurchasable()
    {
        $courseProduct = new CourseProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => ['buyable' => 0]],
            ['functionName' => 'canJoinCourse', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);

        $courseProduct->validate();
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateWithError()
    {
        $courseProduct = new CourseProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => ['buyable' => true]],
            ['functionName' => 'canJoinCourse', 'returnValue' => ['class' => 'Biz\Course\CourseException', 'code' => 'UNBUYABLE_COURSE', 'msg' => 'wrong']],
        ]);

        $courseProduct->validate();
    }

    public function testInit()
    {
        list($goods, $goodsSpecs, $course) = $this->createPublishedCourseGoodsAndGoodsSpecs();

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $product->init(['targetId' => $goodsSpecs['id']]);

        $this->assertEquals($product->targetId, $goodsSpecs['id']);
        $this->assertEquals($product->goods, $goods);
        $this->assertEquals($product->goodsSpecs, $goodsSpecs);
        $this->assertEquals($product->backUrl, ['routing' => 'goods_show', 'params' => ['id' => $goodsSpecs['goodsId'], 'targetId' => $goodsSpecs['targetId']]]);
        $this->assertEquals($product->successUrl, ['routing' => 'my_course_show', 'params' => ['id' => $goodsSpecs['targetId']]]);
        $this->assertEquals($product->title, $goods['title'].'-'.$goodsSpecs['title']);
        $this->assertEquals($product->originPrice, $goodsSpecs['price']);
        $this->assertTrue($product->productEnable);
        $this->assertEquals($product->cover, $goodsSpecs['images']);
    }

    protected function createPublishedCourseGoodsAndGoodsSpecs($goods = [], $goodsSpecs = [], $courseSet = [])
    {
        $courseSet = array_merge([
            'title' => 'test course title',
            'type' => 'normal',
        ], $courseSet);
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);

        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        $course = $this->getCourseService()->getCourse($courseSet['defaultCourseId']);

        $product = $this->getProductService()->getProductByTargetIdAndType($course['id'], 'course');

        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($product['id'], $course['id']);
        $goods = $this->getGoodsService()->getGoods($goodsSpecs['goodsId']);

        return [$goods, $goodsSpecs, $course];
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
