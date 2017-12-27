<?php

namespace Tests\Unit\Component\Export\Bill;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Bill\CashBillExporter;

class CashBillExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new CashBillExporter(self::$appKernel->getContainer(), array());
        $result = $expoter->getTitles();
        $this->assertEquals('cashflow.sn', $result[0]);
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new CashBillExporter(self::$appKernel->getContainer(), array());
        $this->mockBiz(
            'Account:AccountProxyService',
            array(
                array(
                    'functionName' => 'searchCashflows',
                    'returnValue' => array(
                        array('trade_sn' => '201711221', 'buyer_id' => 2, 'type' => 'outflow', 'amount_type' => 'money', 'platform' => 'lianlianpay', 'sn' => '201711221', 'title' => 'title1', 'order_sn' => '201711221', 'created_time' => 50000, 'amount' => 2),
                        array('trade_sn' => '201711222', 'buyer_id' => 2, 'type' => 'inflow', 'amount_type' => 'coin', 'platform' => 'lianlianpay', 'sn' => '201711222', 'title' => 'title2', 'order_sn' => '201711222', 'created_time' => 60000, 'amount' => 2),
                        array('trade_sn' => '201711223', 'buyer_id' => 2, 'type' => 'outflow', 'amount_type' => 'coin', 'platform' => 'lianlianpay', 'sn' => '201711223', 'title' => 'title3', 'order_sn' => '201711223', 'created_time' => 70000, 'amount' => 2)
                    ),
                    'withParams' => array(
                        array('amount_type' => 'money', 'user_id' => 0),
                        array('id' => 'DESC'),
                        0,
                        5
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Pay:PayService',
            array(
                array(
                    'functionName' => 'findTradesByTradeSn',
                    'returnValue' => array(
                        array('trade_sn' => '201711221', 'platform_sn' => '2017112296940135'),
                    ),
                    'withParams' => array(
                        array('201711221', '201711222', '201711223')
                    ),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'returnValue' => array(
                        2 => array('email' => 'test@edusoho.net', 'nickname' => 'test'),
                    ),
                    'withParams' => array(
                        array(2, 2, 2)
                    ),
                ),
                array(
                    'functionName' => 'findUserProfilesByIds',
                    'returnValue' => array(
                        2 => array('mobile' => '15687654321', 'truename' => 'name'),
                    ),
                    'withParams' => array(
                        array(2, 2, 2)
                    ),
                ),
            )
        );
        $result = $expoter->getContent(0, 5);
        $this->assertEquals('title1', $result[0][1]);
        $this->assertEquals('title2', $result[1][1]);
        $this->assertEquals('title3', $result[2][1]);
    }

    public function testCanExport()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new CashBillExporter(self::$appKernel->getContainer(), array());
        $result = $expoter->canExport();
        $this->assertTrue($result);

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions(array());

        $this->assertFalse($expoter->canExport());
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new CashBillExporter(self::$appKernel->getContainer(), array());
        $this->mockBiz(
            'Account:AccountProxyService',
            array(
                array(
                    'functionName' => 'countCashflows',
                    'returnValue' => 1,
                    'withParams' => array(
                        array('amount_type' => 'money', 'user_id' => 0),
                    ),
                ),
            )
        );
        $result = $expoter->getCount();
        $this->assertEquals(1, $result);
    }
}