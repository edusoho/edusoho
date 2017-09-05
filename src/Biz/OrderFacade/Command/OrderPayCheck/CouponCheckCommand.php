<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Biz\Coupon\Service\CouponService;
use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class CouponCheckCommand extends OrderPayCheckCommand
{
    public function execute($order, $params)
    {
        $deducts = $this->getOrderService()->findOrderItemDeductsByItemId($order['id']);

        foreach ($deducts as $deduct) {
            if ($deduct['deduct_type'] == 'coupon') {
                $coupon = $this->getCouponService()->getCoupon($deduct['deduct_id']);

                if ($coupon['status'] !== 'using') {
                    throw new ServiceException('coupon.had_been_used');
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
