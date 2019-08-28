<?php

namespace Tests\Unit\Distributor\Service;

use Biz\BaseTestCase;
use Biz\OrderFacade\Product\CourseProduct;

class DistributorProductDealerServiceTest extends BaseTestCase
{
    public function testDealBeforeCreateProduct()
    {
        $this->mockDistributorUserService();
        $logger = $this->mockBiz(
            'logger',
            array(
                array(
                    'functionName' => 'info',
                ),
            )
        );

        $this->biz['drp.plugin.logger'] = $logger;

        $token = 'redirect:L2NvdXJzZS8x:1:1:1565751518:dasdaskjd:CtFJ-tHEOkPSj1yabW9nbXo9oKA=';

        $drpUserService = $this->mockBiz(
            'DrpPlugin:Drp:DrpUserService',
            array(
                array(
                    'functionName' => 'trySaveUserDistributorToken',
                    'withParams' => array(),
                    'returnValue' => true,
                ),
            )
        );
        $this->getDistributorProductDealerService()->setParams(
            array(
                'distributor-productOrder-token' => $token,
            )
        );
        $product = new CourseProduct();

        $product = $this->getDistributorProductDealerService()->dealBeforeCreateProduct($product);

        $createExtra = $product->getCreateExtra();
        $this->assertEquals(true, empty($createExtra['distributorToken']));
    }

    private function getDistributorProductDealerService()
    {
        return $this->createService('Distributor:DistributorProductDealerService');
    }

    private function mockDistributorUserService()
    {
        return $this->mockBiz(
            'Distributor:DistributorUserService',
            array(
                array(
                    'functionName' => 'decodeToken',
                    'withParams' => array('redirect:L2NvdXJzZS8x:1:1:1565751518:dasdaskjd:CtFJ-tHEOkPSj1yabW9nbXo9oKA='),
                    'returnValue' => array(
                        'valid' => true,
                    ),
                ),
            )
        );
    }
}
