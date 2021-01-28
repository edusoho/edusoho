<?php

namespace Tests\Unit\Coupon;

use Biz\BaseTestCase;
use Biz\Coupon\Dao\CouponDao;
use Biz\Coupon\Service\CouponService;
use Biz\Coupon\State\UsingCoupon;

class UsingCouponTest extends BaseTestCase
{
    /**
     * @expectedException \AppBundle\Common\Exception\AccessDeniedException
     */
    public function testUsing()
    {
        $coupon = new UsingCoupon($this->getBiz(), array());
        $coupon->using();
    }

    public function testUsed()
    {
        $coupon = $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => 0,
        ));

        $couponState = $this->getCouponService()->getCouponStateById($coupon['id']);

        $couponState->used(array(
            'targetType' => 'course',
            'targetId' => 1,
            'userId' => 1,
            'orderId' => 1,
        ));

        $newCoupon = $this->getCouponDao()->get($coupon['id']);

        $this->assertEquals('used', $newCoupon['status']);
    }

    public function testCancelUsing()
    {
        $coupon = $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'targetType' => '1234',
            'targetId' => 10,
            'orderTime' => 1234,
            'orderId' => 11,
            'deadline' => 0,
        ));

        $couponState = $this->getCouponService()->getCouponStateById($coupon['id']);

        $couponState->cancelUsing();

        $newCoupon = $this->getCouponDao()->get($coupon['id']);

        $this->assertArraySubset(
            array('status' => 'receive'), $newCoupon);
    }

    /**
     * @return CouponDao
     */
    private function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }
}
