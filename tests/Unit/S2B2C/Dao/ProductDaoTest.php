<?php

namespace Tests\Unit\S2B2C\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ProductDaoTest extends BaseDaoTestCase
{
    public function testGetBySupplierIdAndRemoteProductId_whenDataCreated_thenGot()
    {
        $createdProduct = $this->getDao()->create($this->getDefaultMockFields());
        $gotProduct = $this->getDao()->getBySupplierIdAndRemoteProductId($createdProduct['supplierId'], $createdProduct['remoteProductId']);
        $this->assertEquals($createdProduct, $gotProduct);
    }

    public function getDefaultMockFields($customFields = [])
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
}
