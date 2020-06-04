<?php

namespace Tests\Unit\Product\Dao;

use Biz\BaseTestCase;
use Biz\Product\Dao\ProductDao;

class ProductDaoTest extends BaseTestCase
{
    public function testGetByTargetIdAndType()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct('type2');

        $result = $this->getProductDao()->getByTargetIdAndType($product1['targetId'], $product1['targetType']);
        $this->assertEquals($product1, $result);
    }

    public function testFindByIds()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct('type2');
        $product3 = $this->createProduct('type3');

        $result = $this->getProductDao()->findByIds([$product1['id'], $product2['id'], $product3['id'], 1001]);

        $this->assertEquals([$product1, $product2, $product3], $result);
    }

    protected function createProduct($targetType = 'testType', $targetId = 1, $title = 'testTitle', $owner = '')
    {
        $product = [
            'targetType' => $targetType,
            'targetId' => $targetId,
            'title' => $title,
            'owner' => empty($owner) ? (int) $this->getCurrentUser()->getId() : $owner,
        ];

        return $this->getProductDao()->create($product);
    }

    /**
     * @return ProductDao
     */
    protected function getProductDao()
    {
        return $this->createDao('Product:ProductDao');
    }
}
