<?php

namespace Tests\Unit\Coupon;

use Biz\BaseTestCase;
use Biz\Coupon\CouponServiceProvider;

class CouponServiceProviderTest extends BaseTestCase
{
    public function testGetCouponClass()
    {
        $biz = $this->getBiz();
        $biz->register(new CouponServiceProvider());

        $couponFactory = $biz['coupon_factory'];

        $couponObj = $couponFactory('vip');
        $this->assertInstanceOf('Biz\Coupon\Type\VipCoupon', $couponObj);

        $couponObj = $couponFactory('course');
        $this->assertInstanceOf('Biz\Coupon\Type\CourseCoupon', $couponObj);

        $couponObj = $couponFactory('classroom');
        $this->assertInstanceOf('Biz\Coupon\Type\ClassroomCoupon', $couponObj);
    }

    /**
     * @expectedException \Biz\Coupon\CouponException
     * @expectedExceptionMessage exception.coupon.type_invalid
     */
    public function testGetCouponClassError()
    {
        $biz = $this->getBiz();
        $biz->register(new CouponServiceProvider());

        $couponFactory = $biz['coupon_factory'];

        $couponObj = $couponFactory('abc');
    }
}
