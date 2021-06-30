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
        $product1 = $this->mockMultiClassProduct(['title' => '系统默认', 'type' => 'default', 'remark' => 'default product 1']);
        $product2 = $this->mockMultiClassProduct(['title' => 'product 1', 'type' => 'normal', 'remark' => 'product 1']);
        $product3 = $this->mockMultiClassProduct(['title' => 'product 2', 'type' => 'normal', 'remark' => 'product 2']);

        $result = $this->getMulticlassProductDao()->findByIds([$product1['id'], $product2['id'], $product3['id']]);

        $this->assertEquals(3, count($result));
    }

    public function testGetByType()
    {
        $this->batchCreateProduct();

        $result = $this->getMulticlassProductDao()->getByType('default');

        $this->assertEquals('default', $result['type']);
    }

    protected function mockMultiClassProduct($fields)
    {
        return $this->getMulticlassProductDao()->create($fields);
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
