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

        /*
         * 分销系统生成的token {内容类型}:{内容数据}:{网校ID}:{代理商ID}:{时间戳}:{随机数}:{签名}
         *  $splitStr = explode(':', $token);
            $data = array(
                'redirect_type' => $splitStr[0],
                'redirect_content' => $splitStr[1],
                'merchant_id' => $splitStr[2],
                'agency_id' => $splitStr[3],
            );
        *   利用云平台api：cloud_access_key cloud_secret_key加密数据
        */
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
