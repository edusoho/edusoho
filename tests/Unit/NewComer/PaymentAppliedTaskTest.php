<?php

namespace Tests\Unit\NewComer;

use Biz\NewComer\PaymentAppliedTask;
use Biz\BaseTestCase;

class PaymentAppliedTaskTest extends BaseTestCase
{
    public function testGetStatusFalseByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' =>
                        ['payment_applied_task' => ['status' => []]]
                ]
            ]
        );

        $task = new PaymentAppliedTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(false, $result);
    }

    public function testGetStatusTrueByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' =>
                        ['payment_applied_task' => ['status' => 1]]
                ]
            ]
        );

        $task = new PaymentAppliedTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(true, $result);
    }

    public function testGetStatusTrueByPaymentAlipay()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' =>
                        ['alipay_enabled' => 1]
                ],
                [
                    'functionName' => 'set',
                    'returnValue' => null
                ]
            ]
        );

        $count = new PaymentAppliedTask($this->getBiz());
        $result = $count->getStatus();

        $this->assertEquals(true, $result);
    }

    public function testGetStatusTrueByPaymentWxpay()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' =>
                        ['wxpay_enabled' => 1]
                ],
                [
                    'functionName' => 'set',
                    'returnValue' => null
                ]
            ]
        );

        $count = new PaymentAppliedTask($this->getBiz());
        $result = $count->getStatus();

        $this->assertEquals(true, $result);
    }

    public function testGetStatusTrueByPaymenLLpay()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' =>
                        ['llpay_enabled' => 1]
                ],
                [
                    'functionName' => 'set',
                    'returnValue' => null
                ]
            ]
        );

        $count = new PaymentAppliedTask($this->getBiz());
        $result = $count->getStatus();

        $this->assertEquals(true, $result);
    }

}