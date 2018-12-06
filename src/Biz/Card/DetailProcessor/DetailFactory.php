<?php

namespace Biz\Card\DetailProcessor;

use Biz\Card\CardException;

class DetailFactory
{
    /**
     * @param $cardType
     *
     * @return DetailProcessor
     *
     * @throws CardException
     */
    public static function create($cardType)
    {
        if (empty($cardType) || !in_array($cardType, array('coupon', 'moneyCard'))) {
            throw CardException::TYPE_INVALID();
        }

        if ($cardType == 'coupon') {
            $class = 'Biz\\Coupon\\CouponProcessor\\CouponDetailProcessor';
        }

        if ($cardType == 'moneyCard') {
            $class = 'Biz\\MoneyCard\\MoneyCardProcessor\\MoneyCardDetailProcessor';
        }

        return new $class();
    }
}
