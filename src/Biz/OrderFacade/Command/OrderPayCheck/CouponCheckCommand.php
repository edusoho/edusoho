<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Order\Service\OrderService;

class CouponCheckCommand extends OrderPayCheckCommand
{
    public function execute($order, $params)
    {
        $deducts = $this->getOrderService()->findOrderItemDeductsByItemId($order['id']);

        foreach ($deducts as $deduct) {
            if ($deduct['deduct_type'] == 'coupon') {
                $coupon = $this->getCouponService()->getCoupon($deduct['deduct_id']);

                if ($coupon['status'] !== 'using') {
                    throw new OrderPayCheckException('order.pay_check_msg.coupon_had_been_used', 2003);
                }
            }
        }
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }
}
