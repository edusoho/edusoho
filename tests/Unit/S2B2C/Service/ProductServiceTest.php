<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\ProductService;

class ProductServiceTest extends BaseTestCase
{
    public function testCreateProduct_whenParamsCorrect_thenCreated()
    {
        $product = $this->mockProductFields();
        $savedProduct = $this->getS2B2CProductService()->createProduct($product);
        $this->assertEquals($product['supplierId'], $savedProduct['supplierId']);
    }

    public function testsGetProduct_whenCreated_thenGot()
    {
        $savedProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $getProduct = $this->getS2B2CProductService()->getProduct($savedProduct['id']);
        $this->assertEquals($savedProduct, $getProduct);
    }

    public function testGetBySupplierIdAndRemoteProductId_whenDataCreated_thenGot()
    {
        $createdProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $gotProduct = $this->getS2B2CProductService()->getProductBySupplierIdAndRemoteProductId($createdProduct['supplierId'], $createdProduct['remoteProductId']);
        $this->assertEquals($createdProduct, $gotProduct);
    }

    public function testFindProductsBySupplierIdAndRemoteProductIds_whenDataCreated_thenFound()
    {
        $createdProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $findProducts = $this->getS2B2CProductService()->findProductsBySupplierIdAndRemoteProductIds($createdProduct['supplierId'], [$createdProduct['remoteProductId']]);
        $this->assertCount(1, $findProducts);
        $this->assertEquals($createdProduct, reset($findProducts));
    }

    public function testFindProductsBySupplierIdAndRemoteResourceTypeAndIds_whenCreated_thenFound()
    {
        $createdProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $findProducts = $this->getS2B2CProductService()->findProductsBySupplierIdAndRemoteResourceTypeAndIds($createdProduct['supplierId'], $createdProduct['productType'], [$createdProduct['remoteResourceId']]);
        $this->assertCount(1, $findProducts);
        $this->assertEquals($createdProduct, reset($findProducts));
    }

    public function testFindProductsBySupplierIdAndProductTypeAndLocalResourceIds_whenCreated_thenFound()
    {
        $createdProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $findProducts = $this->getS2B2CProductService()->findProductsBySupplierIdAndProductTypeAndLocalResourceIds($createdProduct['supplierId'], $createdProduct['productType'], [$createdProduct['localResourceId']]);
        $this->assertCount(1, $findProducts);
        $this->assertEquals($createdProduct, reset($findProducts));
    }

    public function testDeleteByIds()
    {
        $product = $this->getS2B2CProductService()->createProduct($this->mockProductFields());

        $result = $this->getS2B2CProductService()->deleteByIds([$product['id'], 2, 3]);
        $this->assertEquals(1, $result);
    }

    protected function mockProductFields($customFields = [])
    {
        return array_merge([
            'supplierId' => 1,
            'productType' => 'course',
            'remoteProductId' => 1,
            'remoteResourceId' => 1,
            'localResourceId' => 1,
            'cooperationPrice' => (float) 2.00,
            'suggestionPrice' => (float) 3.00,
            'localVersion' => 1,
        ], $customFields);
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }
}
