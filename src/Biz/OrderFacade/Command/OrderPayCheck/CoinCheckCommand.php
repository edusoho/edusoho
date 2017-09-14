<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Currency;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Framework\Pay\Service\AccountService;

class CoinCheckCommand extends OrderPayCheckCommand
{
    public function execute($order, $params)
    {
        if (empty($params['coinAmount'])) {
            return;
        }

        if (empty($params['payPassword'])) {
            throw new OrderPayCheckException('order.pay_check_msg.missing_pay_password', 2000);
        }

        $user = $this->biz['user'];

        $balance = $this->getAccountService()->getUserBalanceByUserId($user->getId());

        if ($balance['amount'] < $params['coinAmount']) {
            throw new OrderPayCheckException('order.pay_check_msg.balance_not_enough', 2001);
        }

        if ($this->getAccountService()->isSecurityAnswersSetted($user->getId())) {
            throw new OrderPayCheckException('order.pay_check_msg.pay_password_not_set', 2008);
        }

        $isCorrect = $this->getAccountService()->validatePayPassword($user->getId(), $params['payPassword']);

        if (!$isCorrect) {
            throw new OrderPayCheckException('order.pay_check_msg.incorrect_pay_password', 2002);
        }
    }

    /**
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }

    /**
     * @return Currency
     */
    private function getCurrency()
    {
        return $this->biz['currency'];
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }
}
