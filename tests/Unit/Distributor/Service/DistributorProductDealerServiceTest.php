<?php

namespace Tests\Unit\Distributor\Service;

use Biz\BaseTestCase;
use Biz\OrderFacade\Product\CourseProduct;

class DistributorProductDealerServiceTest extends BaseTestCase
{
    public function testDealBeforeCreateProduct()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('developer', array()),
                    'returnValue' => array(
                    ),
                ),
            )
        );

        $token = 'courseOrder:9:123:333:1524324352:c9a10dc1737f63a43d2ca6d155155999:2DQ1xlkUFVceNkn_QLOvf3acM8w=';
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
