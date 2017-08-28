<?php

namespace Tests\Unit\Coupon;

use Biz\BaseTestCase;

class CouponServiceTest extends BaseTestCase
{
    public function testGetDeductAmount()
    {
        $coupon = array(
            'type' => 'minus',
            'rate' => 10,
        );

        $deductAmount = $this->getCouponService()->getDeductAmount($coupon, 1);
        $this->assertEquals(10, $deductAmount);

        $coupon['type'] = 'discount';
        $coupon['rate'] = '2';
        $deductAmount = $this->getCouponService()->getDeductAmount($coupon, 10);
        $this->assertEquals(8, $deductAmount);
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }
}
