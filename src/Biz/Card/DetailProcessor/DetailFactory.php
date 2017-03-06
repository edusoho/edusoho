<?php

namespace Biz\Card\DetailProcessor;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class DetailFactory
{
    /**
     * @param $cardType
     *
     * @return DetailProcessor
     *
     * @throws InvalidArgumentException
     */
    public static function create($cardType)
    {
        if (empty($cardType) || !in_array($cardType, array('coupon', 'moneyCard'))) {
            throw new InvalidArgumentException('卡的类型不存在');
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
