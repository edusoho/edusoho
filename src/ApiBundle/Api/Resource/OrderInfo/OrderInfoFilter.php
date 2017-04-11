<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\Resource\Coupon\CouponFilter;
use ApiBundle\Api\Resource\Filter;

class OrderInfoFilter extends Filter
{
    protected function customFilter(&$data)
    {
        $couponFilter = new CouponFilter();
        $couponFilter->filters($data['availableCoupons']);
        $orderInfo = array(
            'targetId' => $data['targetId'],
            'targetType' => $data['targetType'],
            'totalPrice' => $data['totalPrice'],
            'title' => $data[$data['targetType']]['title'],
            'account' => empty($data['account']) ? array() : $data['account'],
            'hasPayPassword' => empty($data['hasPayPassword']) ? 0 : 1,
            'verifiedMobile' => empty($data['verifiedMobile']) ? '' : $data['verifiedMobile'],
            'cashRate' => empty($data['cashRate']) ? 0 : $data['cashRate'],
            'priceType' => empty($data['priceType']) ? 'RMB' : $data['priceType'],
            'coinPayAmount' => empty($data['coinPayAmount']) ? 0 : $data['coinPayAmount'],
            'maxCoin' => empty($data['maxCoin']) ? 0 : $data['maxCoin'],
            'availableCoupons' => $data['availableCoupons'],
            'fullCoinPayable' => $this->fullCoinPayable($data)
        );

        $data = $orderInfo;
    }

    private function fullCoinPayable($data)
    {
        if ($data['priceType'] == 'Coin') {
            return 1;
        }

        if ($data['cashRate'] != 0 && $data[$data['targetType']]['maxRate'] == 100) {
            return 1;
        }

        return 0;
    }
}