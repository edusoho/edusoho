<?php

namespace Biz\OrderFacade\Exception;

use AppBundle\Common\Exception\AbstractException;

class OrderPayCheckException extends AbstractException
{
    const EXCEPTION_MODULE = 26;

    const ERROR_COIN_AMOUNT = 5002601;
    const MISSING_PAY_PASSWORD = 5002602;
    const NOT_ENOUGH_BALANCE = 5002603;
    const NOTFOUND_PAY_PASSWORD = 4042604;
    const ERROR_PAY_PASSWORD = 5002605;
    const OUT_OF_MAX_COIN = 5002606;
    const UNPURCHASABLE_PRODUCT = 5002607;
    const COUPON_HAD_BEEN_USED = 5002608;
    const NOTFOUND_PRODUCT = 4042609;
    const INSTANCE_ERROR = 5002610;

    public $messages = [
        5002601 => 'order.pay_check_msg.coin_amount_error',
        5002602 => 'order.pay_check_msg.missing_pay_password',
        5002603 => 'order.pay_check_msg.balance_not_enough',
        4042604 => 'order.pay_check_msg.pay_password_not_set',
        5002605 => 'order.pay_check_msg.incorrect_pay_password',
        5002606 => 'order.pay_check_msg.out_of_max_coin',
        5002607 => 'order.pay_check_msg.unpurchasable_product',
        5002608 => 'order.pay_check_msg.coupon_had_been_used',
        4042609 => 'order.pay_check_msg.product_not_found',
        5002610 => 'exception.order_pay_check.instance_error',
    ];
}
