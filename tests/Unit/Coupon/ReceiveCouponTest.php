<?php

namespace Tests\Unit\Coupon;

use Biz\BaseTestCase;
use Biz\Coupon\Dao\CouponDao;
use Biz\Coupon\Service\CouponService;
use Biz\Coupon\State\ReceiveCoupon;

class ReceiveCouponTest extends BaseTestCase
{
    public function testUsing()
    {
        $coupon = $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'receive',
            'rate' => 10,
            'deadline' => 0,
        ));

        $couponState = $this->getCouponService()->getCouponStateById($coupon['id']);

        $couponState->using();

        $newCoupon = $this->getCouponDao()->get($coupon['id']);

        $this->assertArraySubset(
            array(
                'status' => 'using',
            ), $newCoupon);
    }

    /**
     * @expectedException \AppBundle\Common\Exception\AccessDeniedException
     */
    public function testUsed()
    {
        $coupon = new ReceiveCoupon($this->getBiz(), array());
        $coupon->used(array());
    }

    /**
     * @expectedException \AppBundle\Common\Exception\AccessDeniedException
     */
    public function testCancelUsing()
    {
        $coupon = new ReceiveCoupon($this->getBiz(), array());
        $coupon->cancelUsing();
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
