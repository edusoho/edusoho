<?php

namespace Tests\Unit\MultiClass\Dao;

use Biz\BaseTestCase;
use Biz\MultiClass\Dao\MultiClassProductDao;

class MultiClassProductDaoTest extends BaseTestCase
{
    public function testGetByTitle()
    {
        $this->batchCreateProduct();

        $result = $this->getMulticlassProductDao()->getByTitle('product 1');

        $this->assertEquals('product 1', $result['title']);
    }

    public function testFindByIds()
    {
        $this->batchCreateProduct();

        $result = $this->getMulticlassProductDao()->findByIds([1, 3, 10]);

        $this->assertEquals(2, count($result));
    }

    public function testGetByType()
    {
        $this->batchCreateProduct();

        $result = $this->getMulticlassProductDao()->getByType('default');

        $this->assertEquals('default', $result['type']);
    }

    protected function batchCreateProduct()
    {
        return $this->getMulticlassProductDao()->batchCreate([
            [
                'title' => '系统默认',
                'type' => 'default',
                'remark' => 'default product 1',
            ],
            [
                'title' => 'product 1',
                'type' => 'normal',
                'remark' => 'product 1',
            ],
            [
                'title' => 'product 2',
                'type' => 'normal',
                'remark' => 'product 2',
            ],
            [
                'title' => '班课产品',
                'type' => 'normal',
                'remark' => 'product 3',
            ],
        ]);
    }

    /**
     * @return MultiClassProductDao
     */
    protected function getMulticlassProductDao()
    {
        return $this->createDao('MultiClass:MultiClassProductDao');
    }
}
