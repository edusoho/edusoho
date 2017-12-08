<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\OrderPayCheck\CouponCheckCommand;

class CouponCheckCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $command = new CouponCheckCommand();
        $command->setBiz($this->getBiz());

        $this->mockBiz('Order:OrderService', array(
            array(
                'functionName' => 'findOrderItemDeductsByItemId',
                'returnValue' => array(array('id' => 1, 'deduct_type' => 'coupon', 'deduct_id' => 2)),
            ),
        ));

        $this->mockBiz('Coupon:CouponService', array(
            array(
                'functionName' => 'getCoupon',
                'returnValue' => array('status' => 'using'),
            ),
        ));

        $result = $command->execute(array('id' => 1), array());

        $this->assertNull($result);
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     * @expectedExceptionMessage order.pay_check_msg.coupon_had_been_used
     */
    public function testExecuteCoinAmountNegative()
    {
        $command = new CouponCheckCommand();
        $command->setBiz($this->getBiz());

        $this->mockBiz('Order:OrderService', array(
            array(
                'functionName' => 'findOrderItemDeductsByItemId',
                'returnValue' => array(array('id' => 1, 'deduct_type' => 'coupon', 'deduct_id' => 2)),
            ),
        ));

        $this->mockBiz('Coupon:CouponService', array(
            array(
                'functionName' => 'getCoupon',
                'returnValue' => array('status' => 'unuse'),
            ),
        ));

        $result = $command->execute(array('id' => 1), array());
    }
}
