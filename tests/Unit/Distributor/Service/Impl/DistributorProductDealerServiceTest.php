<?php

namespace Tests\Unit\Distributor\Service\Impl;

use Biz\BaseTestCase;
use Biz\OrderFacade\Product\CourseProduct;

class DistributorProductDealerServiceTest extends BaseTestCase
{
    public function testDealBeforeCreateProduct()
    {
        $token = 'courseOrder:9:333:123:1524313483:8a4323be2ae4d5b7fa1bec53c43b203c:Sgts-yLzLy5PH5c2NJ_s2Xdd_4U=';
        $this->getDistributorProductDealerService()->setParams(
            array(
                'distributor-productOrder-token' => $token,
            )
        );

        $product = new CourseProduct();
        $product = $this->getDistributorProductDealerService()->dealBeforeCreateProduct($product);

        $createExtra = $product->getCreateExtra();
        $this->assertEquals($token, $createExtra['distributorToken']);
    }

    private function getDistributorProductDealerService()
    {
        return $this->createService('Distributor:DistributorProductDealerService');
    }
}
