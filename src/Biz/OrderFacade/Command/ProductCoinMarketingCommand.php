<?php

namespace Biz\OrderFacade\Command;

use AppBundle\Common\NumberToolkit;
use Biz\Cash\Service\CashAccountService;
use Biz\OrderFacade\Product\Product;
use Biz\System\Service\SettingService;

class ProductCoinMarketingCommand extends Command
{
    public function execute(Product $product)
    {
        list($coinEnable, $priceType, $cashRate) = $this->getCoinSetting();

        $totalPrice = 0;

        if (!$coinEnable) {
            return;
        }

        if ($priceType == 'Coin') {
            $coinSetting = $this->getSettingService()->get('coin');
            $coinEnable = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'] == 1;

            if ($coinEnable && array_key_exists('cash_rate', $coinSetting)) {
                $cashRate = $coinSetting['cash_rate'];
            }

            $totalPrice = $product->price * $cashRate;
        } elseif ($priceType == 'RMB') {
            $totalPrice = $product->price;
            $maxRate = property_exists($product->maxRate) ? 1 : $product->maxRate;
            $maxCoin = NumberToolkit::roundUp($maxRate * $product->price / 100 * $cashRate);
        }

        list($totalPrice, $coinPayAmount, $account, $hasPayPassword) = $this->calculateCoinAmount($totalPrice, $priceType, $cashRate);

        if (!isset($maxCoin)) {
            $coinMarketing['maxCoin'] = $coinPayAmount;
        } else {
            $coinMarketing['maxCoin'] = $maxCoin;
        }

        $product->price = $totalPrice;

        $coinMarketing = $this->getSettingService()->get('coin', array());
        $currentUser = $this->biz['user'];
        $verifiedMobile = '';

        if ((isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile']) > 0)) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        $coinMarketing['coinPayAmount'] = $coinPayAmount;
        $coinMarketing['account'] = $account;
        $coinMarketing['verifiedMobile'] = $verifiedMobile;
        $coinMarketing['hasPayPassword'] = $hasPayPassword;

        $product->marketing['coin'] = $coinMarketing;
    }

    private function calculateCoinAmount($totalPrice, $priceType, $cashRate)
    {
        $user = $this->biz['user'];

        $account = $this->getCashAccountService()->getAccountByUserId($user['id']);
        $accountCash = empty($account['cash']) ? 0 : $account['cash'];

        $coinPayAmount = 0;

        $hasPayPassword = strlen($user['payPassword']) > 0;

        if ($hasPayPassword) {
            if ($priceType == 'Coin') {
                if ($totalPrice * 100 > $accountCash * 100) {
                    $coinPayAmount = $accountCash;
                } else {
                    $coinPayAmount = $totalPrice;
                }
            } elseif ($priceType == 'RMB') {
                if ($totalPrice * 100 > $accountCash / $cashRate * 100) {
                    $coinPayAmount = $accountCash;
                } else {
                    $coinPayAmount = $totalPrice * $cashRate;
                }
            }
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);
        $coinPayAmount = NumberToolkit::roundUp($coinPayAmount);

        return array($totalPrice, $coinPayAmount, $account, $hasPayPassword);
    }

    /**
     * @return CashAccountService
     */
    private function getCashAccountService()
    {
        return $this->biz->service('Cash:CashAccountService');
    }

    private function getCoinSetting()
    {
        $coinSetting = $this->getSettingService()->get('coin');

        $coinEnable = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'] == 1;

        $cashRate = 1;

        if ($coinEnable && array_key_exists('cash_rate', $coinSetting)) {
            $cashRate = $coinSetting['cash_rate'];
        }

        $priceType = 'RMB';

        if ($coinEnable && !empty($coinSetting) && array_key_exists('price_type', $coinSetting)) {
            $priceType = $coinSetting['price_type'];
        }

        return array($coinEnable, $priceType, $cashRate);
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
