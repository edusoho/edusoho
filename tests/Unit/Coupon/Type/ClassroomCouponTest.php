<?php

namespace Tests\Unit\Coupon\Type;

use Biz\BaseTestCase;

class ClassroomCouponTest extends BaseTestCase
{
    public function testCanUseable()
    {
        $biz = $this->getBiz();
        $couponFactory = $biz['coupon_factory'];
        $classroomCoupon = $couponFactory('classroom');

        $result = $classroomCoupon->canUseable(array('targetId' => 1), array('id' => 1));
        $this->assertTrue($result);

        $result1 = $classroomCoupon->canUseable(array('targetId' => 1), array('id' => 2));
        $this->assertFalse($result1);
    }
}