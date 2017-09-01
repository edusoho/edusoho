<?php

namespace Biz\OrderFacade\Deduct;

use Biz\Coupon\Service\CouponService;
use Codeages\Biz\Framework\Order\Callback\PaidCallback;

class CouponDeduct extends Deduct implements PaidCallback
{
    const TYPE = 'coupon';

    public function paidCallback($orderItemDeduct)
    {
        $params = array(
            'userId' => $orderItemDeduct['user_id'],
            'orderId' => $orderItemDeduct['order_id'],
            'targetType' => '',
            'targetId' => 0,
        );

        if ($orderItemDeduct['item']) {
            $params['targetType'] = $orderItemDeduct['item']['target_type'];
            $params['targetId'] = $orderItemDeduct['item']['target_id'];
        }

        $this->getCouponService()->useCoupon($orderItemDeduct['id'], $params);
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }
}
