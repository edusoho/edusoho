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
        $this->mockBiz('Coupon:CouponBatchResourceService', array(
            array(
                'functionName' => 'isCouponTarget',
                'returnValue' => true,
                'withParams' => array(1, 1),
            ),
            array(
                'functionName' => 'isCouponTarget',
                'returnValue' => false,
                'withParams' => array(1, 2),
            ),
        ));

        $result = $classroomCoupon->canUseable(array('batchId' => 1), array('id' => 1));
        $this->assertTrue($result);

        $result1 = $classroomCoupon->canUseable(array('batchId' => 1), array('id' => 2));
        $this->assertFalse($result1);
    }
}
