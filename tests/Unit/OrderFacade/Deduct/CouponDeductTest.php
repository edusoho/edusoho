<?php

namespace Tests\Unit\OrderFacade\Deduct;

use Biz\BaseTestCase;
use Biz\OrderFacade\Deduct\CouponDeduct;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class CouponDeductTest extends BaseTestCase
{
    public function testOnCreated()
    {
        $coupon = $this->getCouponDao()->create(array(
            'code' => 'Test123456',
            'type' => 'minus',
            'status' => 'receive',
            'rate' => 10,
            'deadline' => time(),
        ));

        $deduct = new CouponDeduct();
        $deduct->setBiz($this->getBiz());

        $deduct->onCreated(array('deduct_id' => $coupon['id']));

        $result = $this->getCouponService()->getCoupon($coupon['id']);

        $this->assertEquals('using', $result['status']);
    }

    public function testOnClosed()
    {
        $coupon = $this->_createCoupon();

        $deduct = new CouponDeduct();
        $deduct->setBiz($this->getBiz());

        $deduct->onClosed(array('deduct_id' => $coupon['id']));
        $result = $this->getCouponService()->getCoupon($coupon['id']);

        $this->assertEquals('receive', $result['status']);
    }

    public function testOnPaid()
    {
        $coupon = $this->_createCoupon();

        $deduct = new CouponDeduct();
        $deduct->setBiz($this->getBiz());

        $user = $this->getCurrentUser();

        $result = $deduct->onPaid(array('deduct_id' => $coupon['id'], 'user_id' => $user['id'], 'order_id' => 1, 'item' => array('target_type' => 'course', 'target_id' => 1)));

        $this->assertEquals(OrderStatusCallback::SUCCESS, $result);
    }

    private function _createCoupon()
    {
        return $this->getCouponDao()->create(array(
            'code' => 'Test123456',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
        ));
    }

    protected function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }

    private function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
    }
}
