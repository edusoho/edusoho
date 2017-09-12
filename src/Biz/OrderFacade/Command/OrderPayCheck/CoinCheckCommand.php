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
            throw new OrderPayCheckException('Missing payPassword', 2000);
        }

        $user = $this->biz['user'];

        $balance = $this->getAccountService()->getUserBalanceByUserId($user->getId());

        if ($balance['amount'] < $params['coinAmount']) {
            throw new OrderPayCheckException('Your balance is insufficient', 2001);
        }

        $isCorrect = $this->getAccountService()->validatePayPassword($user->getId(), $params['payPassword']);

        if (!$isCorrect) {
            throw new OrderPayCheckException('Incorrect payPassword', 2002);
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
