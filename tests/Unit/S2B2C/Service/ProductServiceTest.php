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

    protected function mockProductFields($customFields = [])
    {
        return array_merge([
            'supplierId' => 1,
            'productType' => 'course',
            'remoteProductId' => 1,
            'localProductId' => 1,
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
