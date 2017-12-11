<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Pay\Service\AccountService;

class CoinCheckCommand extends OrderPayCheckCommand
{
    public function execute($order, $params)
    {
        if (empty($params['coinAmount'])) {
            return;
        }

        if ($params['coinAmount'] < 0) {
            throw new OrderPayCheckException('order.pay_check_msg.parameters_error');
        }

        if (empty($params['payPassword'])) {
            throw new OrderPayCheckException('order.pay_check_msg.missing_pay_password', 2000);
        }

        $user = $this->biz['user'];

        $balance = $this->getAccountService()->getUserBalanceByUserId($user->getId());

        if ($balance['amount'] < $params['coinAmount']) {
            throw new OrderPayCheckException('order.pay_check_msg.balance_not_enough', 2001);
        }

        if (!$this->getAccountService()->isPayPasswordSetted($user->getId())) {
            throw new OrderPayCheckException('order.pay_check_msg.pay_password_not_set', 2008);
        }

        $isCorrect = $this->getAccountService()->validatePayPassword($user->getId(), $params['payPassword']);

        if (!$isCorrect) {
            throw new OrderPayCheckException('order.pay_check_msg.incorrect_pay_password', 2002);
        }

        $products = $this->orderPayChecker->getProducts($order);
        foreach ($products as $product) {
            /** @var $product Product */
            if ($params['coinAmount'] > $product->getMaxCoinAmount()) {
                throw new OrderPayCheckException('order.pay_check_msg.out_of_max_coin', 2009);
            }
        }
    }

    /**
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }
}
