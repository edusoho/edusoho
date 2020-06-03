<?php

namespace Tests\Unit\Product\Service;

use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\Product\Dao\ProductDao;
use Biz\Product\Service\ProductService;

class ProductServiceTest extends BaseTestCase
{
    public function testCreateProduct_whenMissingTargetType_thenThrowParamMissingException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $this->getProductService()->createProduct(['targetId' => 1, 'title' => 'testTitle']);
    }

    public function testCreateProduct_whenMissingTargetId_thenThrowParamMissingException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $this->getProductService()->createProduct(['targetType' => 'testType', 'title' => 'testTitle']);
    }

    public function testCreateProduct_whenMissingTitle_thenThrowParamMissingException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $this->getProductService()->createProduct(['targetType' => 'testType', 'targetId' => 1]);
    }

    public function testCreateProduct()
    {
        $result = $this->getProductService()->createProduct([
            'targetType' => 'testType',
            'targetId' => 1,
            'title' => 'testTitle',
        ]);

        $this->assertEquals('testType', $result['targetType']);
        $this->assertEquals(1, $result['targetId']);
        $this->assertEquals('testTitle', $result['title']);
        $this->assertEquals($this->getCurrentUser()->getId(), $result['owner']);
    }

    public function testGetProduct()
    {
        $expected = $this->createProduct();

        $result = $this->getProductService()->getProduct($expected['id']);

        $this->assertArrayEquals($expected, $result);

        $result2 = $this->getProductService()->getProduct($expected['id'] + 100000);
        $this->assertNull($result2);
    }

    public function testUpdateProduct()
    {
        $product = $this->createProduct();

        $fields = [
            'targetType' => 'test update',
            'targetId' => 3,
            'title' => 'test update title',
        ];

        $result = $this->getProductService()->updateProduct($product['id'], $fields);
        $this->assertEquals($product['targetType'], $result['targetType']);
        $this->assertEquals($product['targetId'], $result['targetId']);
        $this->assertNotEquals($product['title'], $result['title']);
        $this->assertEquals($fields['title'], $result['title']);
    }

    public function testDeleteProduct()
    {
        $product = $this->createProduct();
        $before = $this->getProductDao()->get($product['id']);
        $this->assertNotEmpty($before);

        $this->getProductService()->deleteProduct($product['id']);

        $after = $this->getProductDao()->get($product['id']);
        $this->assertEmpty($after);
    }

    public function testSearchProducts_withDifferentConditions()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct('testType2');
        $product3 = $this->createProduct('testType', 2);
        $product4 = $this->createProduct('testType', 1, 'testTitle2');

        $conditions1 = ['titleLike' => 'test'];

        $result1 = $this->getProductService()->searchProducts($conditions1, ['id' => 'ASC'], 0, 10);

        $this->assertArrayEquals([$product1, $product2, $product3, $product4], $result1);

        $conditions2 = ['targetType' => 'testType'];
        $result2 = $this->getProductService()->searchProducts($conditions2, ['id' => 'ASC'], 0, 10);

        $this->assertArrayEquals([$product1, $product3, $product4], $result2);

        $conditions3 = ['targetType' => 'testType', 'title' => 'testTitle2'];
        $result3 = $this->getProductService()->searchProducts($conditions3, ['id' => 'ASC'], 0, 10);

        $this->assertArrayEquals([$product4], $result3);
    }

    public function testSearchProducts_withDifferentOrderBysAndLimits()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct('testType2');
        $product3 = $this->createProduct('testType', 2);
        $product4 = $this->createProduct('testType', 1, 'testTitle2');

        $result1 = $this->getProductService()->searchProducts(['targetType' => 'testType'], ['id' => 'ASC'], 0, 10);
        $this->assertArrayEquals([$product1, $product3, $product4], $result1);

        $result2 = $this->getProductService()->searchProducts(['targetType' => 'testType'], ['id' => 'DESC'], 0, 10);
        $this->assertArrayEquals([$product4, $product3, $product1], $result2);

        $result3 = $this->getProductService()->searchProducts(['targetType' => 'testType'], ['id' => 'ASC'], 0, 2);
        $this->assertArrayEquals([$product1, $product3], $result3);
    }

    public function testSearchProducts_withDifferentColumns()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct('testType2');
        $product3 = $this->createProduct('testType', 2);
        $product4 = $this->createProduct('testType', 1, 'testTitle2');

        $expected1 = [
            ['targetType' => $product1['targetType'], 'targetId' => $product1['targetId']],
            ['targetType' => $product3['targetType'], 'targetId' => $product3['targetId']],
            ['targetType' => $product4['targetType'], 'targetId' => $product4['targetId']],
        ];

        $result1 = $this->getProductService()->searchProducts(['targetType' => 'testType'], ['id' => 'ASC'], 0, 10, ['targetId', 'targetType']);
        $this->assertArrayEquals($expected1, $result1);

        $expected2 = [
            ['targetType' => $product1['targetType'], 'title' => $product1['title']],
            ['targetType' => $product3['targetType'], 'title' => $product3['title']],
            ['targetType' => $product4['targetType'], 'title' => $product4['title']],
        ];

        $result2 = $this->getProductService()->searchProducts(['targetType' => 'testType'], ['id' => 'ASC'], 0, 10, ['targetType', 'title']);
        $this->assertArrayEquals($expected2, $result2);
    }

    public function testGetProductByTargetIdAndType()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct('testType2');

        $result = $this->getProductService()->getProductByTargetIdAndType($product1['targetId'], $product1['targetType']);
        $this->assertArrayEquals($product1, $result);
    }

    public function testFindProductsByIds()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct('testType2');
        $product3 = $this->createProduct('testType', 2);
        $product4 = $this->createProduct('testType', 1, 'testTitle2');

        $result = $this->getProductService()->findProductsByIds([$product1['id'], $product2['id'], $product3['id']]);

        $this->assertArrayEquals(
            [
                $product1['id'] => $product1,
                $product2['id'] => $product2,
                $product3['id'] => $product3,
            ],
            $result
        );
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

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }
}
