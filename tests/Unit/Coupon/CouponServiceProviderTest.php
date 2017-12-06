<?php

namespace Tests\Unit\Coupon;

use Biz\BaseTestCase;
use Biz\Coupon\CouponServiceProvider;
use Biz\Coupon\Type\VipCoupon;
use Biz\Coupon\Type\CourseCoupon;
use Biz\Coupon\Type\ClassroomCoupon;

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
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage support vip or course, you give:abc
     */
    public function testGetCouponClassError()
    {
        $biz = $this->getBiz();
        $biz->register(new CouponServiceProvider());

        $couponFactory = $biz['coupon_factory'];

        $couponObj = $couponFactory('abc');
    }
    
}
