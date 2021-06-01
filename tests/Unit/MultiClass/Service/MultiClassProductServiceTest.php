<?php


namespace Tests\Unit\MultiClass\Service;


use Biz\BaseTestCase;
use Biz\MultiClass\Dao\MultiClassProductDao;
use Biz\MultiClass\Service\MultiClassProductService;

class MultiClassProductServiceTest extends BaseTestCase
{
    public function testGetProductByTitle()
    {
        $this->batchCreateProduct();

        $result = $this->getMultiClassProductService()->getProductByTitle('product 1');

        $this->assertEquals('product 1', $result['title']);
    }

    public function testCreateProduct()
    {
        $expect = [
            'title' => '班课产品1',
            'remark' => '班课产品111'
        ];
        $result = $this->getMultiClassProductService()->createProduct($expect);

        $this->assertEquals($expect['title'], $result['title']);
        $this->assertEquals($expect['remark'], $result['remark']);
    }

    public function testSearchProducts()
    {
       $this->batchCreateProduct();

       $result = $this->getMultiClassProductService()->searchProducts(['keywords' => 'product'], [], 0, PHP_INT_MAX);

       $this->assertEquals(2, count($result));
    }

    public function testCountProducts()
    {
        $this->batchCreateProduct();

        $result = $this->getMultiClassProductService()->countProducts(['keywords' => 'product']);

        $this->assertEquals(2, $result);
    }

    public function testGetProduct()
    {
        $this->batchCreateProduct();

        $result = $this->getMultiClassProductService()->getProduct(1);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('default', $result['type']);
    }

    public function testUpdateProduct()
    {
        $this->batchCreateProduct();

        $result = $this->getMultiClassProductService()->updateProduct(1, ['title' => 'default']);

        $this->assertEquals('default', $result['title']);
    }

    public function testDeleteProduct()
    {
        $this->batchCreateProduct();

        $this->getMultiClassProductService()->deleteProduct(2);
        $result = $this->getMultiClassProductService()->getProduct(2);

        $this->assertEmpty($result);
    }

    public function testFindProductByIds()
    {
        $this->batchCreateProduct();

        $result = $this->getMultiClassProductService()->findProductByIds([1, 3, 10]);

        $this->assertEquals(2, count($result));
    }

    public function testGetDefaultProduct()
    {
        $this->batchCreateProduct();

        $result = $this->getMultiClassProductService()->getDefaultProduct();

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
            ]
        ]);
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->createService('MultiClass:MultiClassProductService');
    }

    /**
     * @return MultiClassProductDao
     */
    protected function getMulticlassProductDao()
    {
        return $this->createDao('MultiClass:MultiClassProductDao');
    }
}