<?php

namespace Topxia\Service\Card\DetailProcessor;

use Exception;
use Topxia\Service\Card\DetailProcessor\DetailProcessor;

class DetailFactory
{
    public static function create($cardType)
    {
        if (empty($cardType) || !in_array($cardType, array('coupon', 'moneyCard'))) {
            throw new Exception("卡的类型不存在");
        }

        if ($cardType == "coupon") {
            $class = "Topxia\Service\Coupon\CouponProcessor\CouponDetailProcessor";
        }

        if ($cardType == "moneyCard") {
            $class = "Topxia\Service\MoneyCard\MoneyCardProcessor\MoneyCardDetailProcessor";
        }

        // $class = __NAMESPACE__ . '\\' . ucfirst($cardType). 'DetailProcessor';
        // 因为coupon和MoneyCard是插件，所以需要减少依赖
        return new $class();
    }
}
