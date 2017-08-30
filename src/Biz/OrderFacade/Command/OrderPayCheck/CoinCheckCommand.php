<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Currency;
use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\AccountService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class CoinCheckCommand extends OrderPayCheckCommand
{
    public function execute($order, $params)
    {
        if (empty($params['coinAmount'])) {
            return;
        }

        if (empty($params['payPassword'])) {
            throw new InvalidArgumentException('Missing payPassword');
        }

        $balance = $this->getAccountService()->getUserBalanceByUserId($this->getCurrentUser()->getId());

        if ($balance['amount'] < $params['coinAmount']) {
            throw new InvalidArgumentException('Bad Coin Amount');
        }

        $isCorrect = $this->getAccountService()->validatePayPassword($this->getCurrentUser()->getId(), $params['payPassword']);

        if (!$isCorrect) {
            throw new InvalidArgumentException('Incorrect payPassword');
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
