<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Codeages\Biz\Pay\Service\AccountService;

class CoinCheckCommand extends OrderPayCheckCommand
{
    public function execute($order, $params)
    {
        if (empty($params['coinAmount'])) {
            return;
        }

        if ($params['coinAmount'] < 0) {
            throw OrderPayCheckException::ERROR_COIN_AMOUNT();
        }

        $cashAmount = $this->getOrderFacadeService()->getTradePayCashAmount($order, $params['coinAmount']);
        if (isset($params['gateway']) && 'Coin' == $params['gateway'] && $cashAmount > 0) {
            throw OrderPayCheckException::ERROR_COIN_AMOUNT();
        }

        if (!isset($params['payPassword'])) {
            throw OrderPayCheckException::MISSING_PAY_PASSWORD();
        }

        $user = $this->biz['user'];

        $balance = $this->getAccountService()->getUserBalanceByUserId($user->getId());

        if ($balance['amount'] < $params['coinAmount']) {
            throw OrderPayCheckException::NOT_ENOUGH_BALANCE();
        }

        if (!$this->getAccountService()->isPayPasswordSetted($user->getId())) {
            throw OrderPayCheckException::NOTFOUND_PAY_PASSWORD();
        }

        $isCorrect = $this->getAccountService()->validatePayPassword($user->getId(), $params['payPassword']);

        if (!$isCorrect) {
            throw OrderPayCheckException::ERROR_PAY_PASSWORD();
        }

        $products = $this->orderPayChecker->getProducts($order);
        foreach ($products as $product) {
            /** @var $product Product */
            if ($params['coinAmount'] > $product->getMaxCoinAmount()) {
                throw OrderPayCheckException::OUT_OF_MAX_COIN();
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

    private function getOrderFacadeService()
    {
        return $this->biz->service('OrderFacade:OrderFacadeService');
    }
}
