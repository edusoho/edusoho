<?php

namespace Tests\Unit\OrderFacade\Exception;

use Biz\BaseTestCase;
use Biz\OrderFacade\Exception\OrderPayCheckException;

class OrderPayCheckExceptionTest extends BaseTestCase
{
    public function testErrorCoinAmount()
    {
        $exception = OrderPayCheckException::ERROR_COIN_AMOUNT();

        $this->assertEquals('order.pay_check_msg.coin_amount_error', $exception->getMessage());
    }

    public function testMissingPayPassword()
    {
        $exception = OrderPayCheckException::MISSING_PAY_PASSWORD();

        $this->assertEquals('order.pay_check_msg.missing_pay_password', $exception->getMessage());
    }

    public function testNotEnoughBalance()
    {
        $exception = OrderPayCheckException::NOT_ENOUGH_BALANCE();

        $this->assertEquals('order.pay_check_msg.balance_not_enough', $exception->getMessage());
    }

    public function testNotFoundPayPassword()
    {
        $exception = OrderPayCheckException::NOTFOUND_PAY_PASSWORD();

        $this->assertEquals('order.pay_check_msg.pay_password_not_set', $exception->getMessage());
    }

    public function testErrorPayPassword()
    {
        $exception = OrderPayCheckException::ERROR_PAY_PASSWORD();

        $this->assertEquals('order.pay_check_msg.incorrect_pay_password', $exception->getMessage());
    }

    public function testOutOfMaxCoin()
    {
        $exception = OrderPayCheckException::OUT_OF_MAX_COIN();

        $this->assertEquals('order.pay_check_msg.out_of_max_coin', $exception->getMessage());
    }

    public function testUnPurchasableProduct()
    {
        $exception = OrderPayCheckException::UNPURCHASABLE_PRODUCT();

        $this->assertEquals('order.pay_check_msg.unpurchasable_product', $exception->getMessage());
    }

    public function testCouponHadBeenUsed()
    {
        $exception = OrderPayCheckException::COUPON_HAD_BEEN_USED();

        $this->assertEquals('order.pay_check_msg.coupon_had_been_used', $exception->getMessage());
    }

    public function testNotFoundProduct()
    {
        $exception = OrderPayCheckException::NOTFOUND_PRODUCT();

        $this->assertEquals('order.pay_check_msg.product_not_found', $exception->getMessage());
    }

    public function testInstanceError()
    {
        $exception = OrderPayCheckException::INSTANCE_ERROR();

        $this->assertEquals('exception.order_pay_check.instance_error', $exception->getMessage());
    }
}
