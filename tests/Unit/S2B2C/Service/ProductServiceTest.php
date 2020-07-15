<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\S2B2C\Service\ProductService;
use Biz\System\Service\SettingService;

class ProductServiceTest extends BaseTestCase
{
    public function testCreateProduct_whenParamsCorrect_thenCreated()
    {
        $product = $this->mockProductFields();
        $savedProduct = $this->getS2B2CProductService()->createProduct($product);
        $this->assertEquals($product['supplierId'], $savedProduct['supplierId']);
    }

    public function testCreateProduct_whenParamsInvalid_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->getS2B2CProductService()->createProduct([]);
    }

    public function testsGetProduct_whenCreated_thenGot()
    {
        $savedProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $getProduct = $this->getS2B2CProductService()->getProduct($savedProduct['id']);
        $this->assertEquals($savedProduct, $getProduct);
    }

    public function testGetProductBySupplierIdAndRemoteProductId_whenDataCreated_thenGot()
    {
        $createdProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $gotProduct = $this->getS2B2CProductService()->getProductBySupplierIdAndRemoteProductId($createdProduct['supplierId'], $createdProduct['remoteProductId']);
        $this->assertEquals($createdProduct, $gotProduct);
    }

    public function testGetProductBySupplierIdAndRemoteResourceIdAndType_whenDataCreated_thenGot()
    {
        $createdProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $gotProduct = $this->getS2B2CProductService()->getProductBySupplierIdAndRemoteResourceIdAndType($createdProduct['supplierId'], $createdProduct['remoteResourceId'], $createdProduct['productType']);
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

    public function testGetProductBySupplierIdAndRemoteProductIdAndType()
    {
        $newProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $product = $this->getS2B2CProductService()->getProductBySupplierIdAndRemoteProductIdAndType($newProduct['supplierId'], $newProduct['remoteProductId'], 'course');
        $this->assertEquals($product['id'], $newProduct['id']);
    }

    public function testGetByProductIdAndRemoteResourceIdAndType()
    {
        $newProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields(['remoteResourceId' => 8]));
        $product = $this->getS2B2CProductService()->getByProductIdAndRemoteResourceIdAndType($newProduct['supplierId'], $newProduct['remoteResourceId'], 'course');
        $this->assertEquals($product['id'], $newProduct['id']);
    }

    public function testGetProductBySupplierIdAndLocalResourceIdAndType()
    {
        $newProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields(['localResourceId' => 999]));
        $product = $this->getS2B2CProductService()->getProductBySupplierIdAndLocalResourceIdAndType($newProduct['supplierId'], $newProduct['localResourceId'], 'course');
        $this->assertEquals($product['id'], $newProduct['id']);
    }

    public function testFindProductsBySupplierIdAndProductType()
    {
        $newProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $product = $this->getS2B2CProductService()->findProductsBySupplierIdAndProductType($newProduct['supplierId'], 'course');
        $this->assertEquals(1, count($product));
    }

    public function testFindProductsBySupplierIdAndRemoteResourceTypeAndProductIds()
    {
        $newProduct1 = $this->getS2B2CProductService()->createProduct($this->mockProductFields(['remoteProductId' => 105]));
        $newProduct2 = $this->getS2B2CProductService()->createProduct($this->mockProductFields(['remoteProductId' => 106]));
        $products = $this->getS2B2CProductService()->findProductsBySupplierIdAndRemoteResourceTypeAndProductIds($newProduct1['supplierId'], 'course', [105, 106]);
        $this->assertEquals(2, count($products));
    }

    public function testUpdateProduct()
    {
        $newProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $product = $this->getS2B2CProductService()->updateProduct($newProduct['id'], ['remoteProductId' => 116]);
        $this->assertEquals(116, $product['remoteProductId']);
    }

    public function testDeleteProduct()
    {
        $newProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields());
        $product = $this->getS2B2CProductService()->deleteProduct($newProduct['id']);
        $this->assertEquals(1, $product);
    }

    public function testSearchRemoteProducts()
    {
        $this->biz['supplier.platform_api'] = $this->mockBiz(
            'supplier.platform_api',
            [
                [
                    'functionName' => 'searchSupplierProducts',
                    'returnValue' => [
                        'paging' => ['total' => 1],
                        'data' => [['id' => 134]],
                    ],
                    'withParams' => [['title' => 'course', 'merchant_access_key' => 'accessKey']],
                ],
            ]
        );

        $this->getSettingService()->set('storage', [
            'cloud_access_key' => 'accessKey',
            'cloud_secret_key' => 'secretKey',
        ]);
        list($courseSets, $total) = $this->getS2B2CProductService()->searchRemoteProducts(['title' => 'course']);
        $this->assertEquals(1, $total);
    }

    public function testSearchProducts()
    {
        $fields = ['remoteProductId' => 154];
        $newProduct1 = $this->getS2B2CProductService()->createProduct($this->mockProductFields($fields));
        $newProduct2 = $this->getS2B2CProductService()->createProduct($this->mockProductFields($fields));
        $products = $this->getS2B2CProductService()->searchProducts($fields, [], 0, PHP_INT_MAX);
        $this->assertEquals(2, count($products));
    }

    public function testCountProducts()
    {
        $fields = ['remoteProductId' => 162];
        $newProduct1 = $this->getS2B2CProductService()->createProduct($this->mockProductFields($fields));
        $newProduct2 = $this->getS2B2CProductService()->createProduct($this->mockProductFields($fields));
        $products = $this->getS2B2CProductService()->countProducts($fields);
        $this->assertEquals(2, $products);
    }

    public function testSearchSelectedProducts()
    {
        $this->biz['supplier.platform_api'] = $this->mockBiz(
            'supplier.platform_api',
            [
                [
                    'functionName' => 'searchPurchaseProducts',
                    'returnValue' => [],
                    'withParams' => [['title' => 'course', 'merchant_access_key' => 'accessKey']],
                ],
            ]
        );

        $this->getSettingService()->set('storage', [
            'cloud_access_key' => 'accessKey',
            'cloud_secret_key' => 'secretKey',
        ]);
        $products = $this->getS2B2CProductService()->searchSelectedProducts(['title' => 'course']);
        $this->assertEquals([], $products);
    }

    public function testGetByTypeAndLocalResourceId()
    {
        $newProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields(['localResourceId' => 193]));
        $product = $this->getS2B2CProductService()->getByTypeAndLocalResourceId('course', 193);
        $this->assertEquals($product['id'], $newProduct['id']);
    }

    public function testSetProductUpdateType_WithAuto()
    {
        $result = $this->getS2B2CProductService()->setProductUpdateType('auto');
        $this->assertEmpty($result);
    }

    public function testSetProductUpdateType_WithManualNoJob()
    {
        $result = $this->getS2B2CProductService()->setProductUpdateType('manual');
        $this->assertEmpty($result);
    }

    public function testSetProductUpdateType_WithManualHasJob()
    {
        $this->getS2B2CProductService()->setProductUpdateType('auto');
        $result = $this->getS2B2CProductService()->setProductUpdateType('manual');
        $this->assertEmpty($result);
    }

    public function testAdoptProduct()
    {
        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);

        $this->biz['qiQiuYunSdk.s2b2cService'] = $this->mockBiz(
            'qiQiuYunSdk.s2b2cService',
            [
                [
                    'functionName' => 'adoptDirtributeProduct',
                    'returnValue' => [
                        'status' => 'success',
                        'data' => ['id' => 226, 'detail' => [['id' => 777, 'supplierId' => 1, 'productId' => 226, 'targetId' => 227, 'targetType' => 'course']]],
                    ],
                    'withParams' => [226],
                ],
            ]
        );

        $this->mockBiz(
            'S2B2C:CourseProductService',
            [
                [
                    'functionName' => 'syncCourses',
                    'returnValue' => [],
                    'withParams' => [226],
                ],
            ]
        );

        $result = $this->getS2B2CProductService()->adoptProduct(226);
        $this->assertEquals(true, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testAdoptProduct_WithRepeat()
    {
        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);

        $newProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields(['remoteProductId' => 266]));
        $product = $this->getS2B2CProductService()->adoptProduct(266);
    }

    /**
     * @expectedException \Exception
     */
    public function testAdoptProduct_WithGetRemoteDataError()
    {
        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);

        $this->biz['qiQiuYunSdk.s2b2cService'] = $this->mockBiz(
            'qiQiuYunSdk.s2b2cService',
            [
                [
                    'functionName' => 'adoptDirtributeProduct',
                    'returnValue' => [
                        'status' => 'error',
                        'data' => [],
                    ],
                    'withParams' => [226],
                ],
            ]
        );

        $product = $this->getS2B2CProductService()->adoptProduct(266);
    }

    /**
     * @expectedException \Exception
     */
    public function testAdoptProduct_WithSyncCourseError()
    {
        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);

        $this->biz['qiQiuYunSdk.s2b2cService'] = $this->mockBiz(
            'qiQiuYunSdk.s2b2cService',
            [
                [
                    'functionName' => 'adoptDirtributeProduct',
                    'returnValue' => [
                        'status' => 'success',
                        'data' => ['id' => 226, 'detail' => [['id' => 777, 'supplierId' => 1, 'productId' => 226, 'targetId' => 227, 'targetType' => 'course']]],
                    ],
                    'withParams' => [226],
                ],
            ]
        );

        $result = $this->getS2B2CProductService()->adoptProduct(226);
    }

    protected function mockProductFields($customFields = [])
    {
        return array_merge([
            'supplierId' => 1,
            'productType' => 'course',
            'remoteProductId' => 1,
            'remoteResourceId' => 1,
            's2b2cProductDetailId' => 1,
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
