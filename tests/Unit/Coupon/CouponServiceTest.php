<?php

namespace Tests\Unit\Coupon;

use Biz\BaseTestCase;
use Biz\Coupon\Dao\CouponDao;
use Biz\Coupon\Service\CouponService;

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
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testGetCouponStateByIdWithError()
    {
        $coupon = $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'used',
            'rate' => 10,
            'deadline' => time(),
        ));

        $this->getCouponService()->getCouponStateById($coupon['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testGetCouponStateByIdWithError3()
    {
        $this->getCouponService()->getCouponStateById(1);
    }

    public function testGetCouponStateById()
    {
        $coupon = $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
        ));

        $this->assertInstanceOf('Biz\Coupon\State\UsingCoupon', $this->getCouponService()->getCouponStateById($coupon['id']));
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
