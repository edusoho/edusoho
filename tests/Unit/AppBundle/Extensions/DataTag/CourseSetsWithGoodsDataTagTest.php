<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseSetsWithGoodsDataTag;
use Biz\BaseTestCase;
use Biz\Course\Dao\CourseSetDao;
use Biz\Goods\Dao\GoodsDao;
use Biz\Product\Dao\ProductDao;

class CourseSetsWithGoodsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $courseSet = $this->createCourseSet();

        $product = $this->createProduct($courseSet);

        $goods = $this->createGoods($product);

        $dataTag = new CourseSetsWithGoodsDataTag();

        $courseSets = $dataTag->getData([$courseSet]);

        $this->assertEquals($goods['ratingNum'], $courseSets[0]['ratingNum']);
    }

    private function createCourseSet()
    {
        $courseSet = [
            'type' => 'normal',
            'title' => 'test',
            'ratingNum' => 0,
        ];

        return $this->getCourseSetDao()->create($courseSet);
    }

    private function createProduct($courseSet)
    {
        $product = [
            'type' => 'course',
            'targetId' => $courseSet['id'],
            'title' => 'test',
        ];

        return $this->getProductDao()->create($product);
    }

    private function createGoods($product)
    {
        $goods = [
            'type' => 'course',
            'productId' => $product['id'],
            'title' => 'test',
            'ratingNum' => 10,
        ];

        return $this->getGoodsDao()->create($goods);
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    /**
     * @return GoodsDao
     */
    private function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }

    /**
     * @return ProductDao
     */
    private function getProductDao()
    {
        return $this->createDao('Product:ProductDao');
    }
}
