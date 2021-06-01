<?php

namespace Tests\Unit\MultiClass\Dao;

use Biz\BaseTestCase;
use Biz\MultiClass\Dao\MultiClassDao;

class MultiClassDaoTest extends BaseTestCase
{
    public function testFindByProductIds()
    {
        $this->batchCreateMultiClass();

        $result = $this->getMultiClassDao()->findByProductIds([1, 2]);

        $this->assertEquals(3, count($result));
    }

    public function testFindByProductId()
    {
        $this->batchCreateMultiClass();

        $result = $this->getMultiClassDao()->findByProductIds(1);

        $this->assertEquals(2, count($result));
    }

    public function testGetByTitle()
    {
        $this->batchCreateMultiClass();

        $result = $this->getMultiClassDao()->getByTitle('班课1');

        $this->assertEquals('班课1', $result['title']);
        $this->assertEquals(1, $result['courseId']);
        $this->assertEquals(1, $result['productId']);
    }

    public function testGetByCourseId()
    {
        $this->batchCreateMultiClass();

        $result = $this->getMultiClassDao()->getByCourseId('班课1');

        $this->assertEquals('班课1', $result['title']);
        $this->assertEquals(1, $result['courseId']);
        $this->assertEquals(1, $result['productId']);
    }

    public function testSearchMultiClassJoinCourse()
    {
        $this->batchCreateMultiClass();

        $result = $this->getMultiClassDao()->searchMultiClassJoinCourse(['productId' => 1], [], 0, 1);

        $this->assertEmpty($result);
    }

    protected function batchCreateMultiClass()
    {
        return $this->getMultiClassDao()->batchCreate([
            [
                'title' => '班课1',
                'courseId' => 1,
                'productId' => 1,
                'copyId' => 0,
            ],
            [
                'title' => '班课2',
                'courseId' => 2,
                'productId' => 1,
                'copyId' => 0,
            ],
            [
                'title' => '班课3',
                'courseId' => 3,
                'productId' => 2,
                'copyId' => 0,
            ],
        ]);
    }

    /**
     * @return MultiClassDao
     */
    protected function getMultiClassDao()
    {
        return $this->createDao('MultiClass:MultiClassDao');
    }
}
