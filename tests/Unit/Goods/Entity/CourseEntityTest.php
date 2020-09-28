<?php

namespace Tests\Unit\Goods\Entity;

use Biz\BaseTestCase;
use Biz\Course\CourseSetException;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\GoodsEntityFactory;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;

class CourseEntityTest extends BaseTestCase
{
    public function testGetTarget()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet($this->mockCourseSet());
        list($product, $goods) = $this->getProductAndGoods($courseSet);
        self::assertEquals($courseSet, $this->getGoodsEntityFactory()->create('course')->getTarget($goods));
    }

    public function testGetTarget_whenProductUnExist()
    {
        $this->expectException(CourseSetException::class);
        $this->expectExceptionCode(CourseSetException::NOTFOUND_COURSESET);
        $courseSet = $this->getCourseSetService()->createCourseSet($this->mockCourseSet());
        list($product, $goods) = $this->getProductAndGoods($courseSet);

        $this->getCourseSetDao()->delete($courseSet['id']);
        $this->getGoodsEntityFactory()->create('course')->getTarget($goods);
    }

    private function getProductAndGoods($courseSet)
    {
        $product = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
        $goods = $this->getGoodsService()->getGoodsByProductId($product['id']);

        return [$product, $goods];
    }

    private function mockCourseSet($customFields = [])
    {
        return array_merge([
            'id' => 1,
            'type' => 'normal',
            'title' => '测试构建商品的课程',
            'subtitle' => '副标题',
            'creator' => 1,
        ], $customFields);
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return GoodsEntityFactory
     */
    protected function getGoodsEntityFactory()
    {
        $biz = $this->biz;

        return $biz['goods.entity.factory'];
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }
}
