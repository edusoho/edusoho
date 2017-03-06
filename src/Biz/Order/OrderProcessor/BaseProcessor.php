<?php

namespace Biz\Order\OrderProcessor;

use Codeages\Biz\Framework\Context\Biz;
use Exception;
use AppBundle\Common\NumberToolkit;

class BaseProcessor
{
    protected $router = 'homepage';

    protected $biz = null;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getTarget($targetId)
    {
        return array();
    }

    /**
     * @return Biz
     */
    public function getBiz()
    {
        return $this->biz;
    }

    public function callbackUrl($order, $container)
    {
        $goto = $container->get('router')->generate($this->router, array('id' => $order['targetId']), true);

        return $goto;
    }

    protected function afterCoinPay($coinEnabled, $priceType, $cashRate, $amount, $coinPayAmount, $payPassword)
    {
        if (!empty($coinPayAmount) && $coinPayAmount > 0 && $coinEnabled) {
            $user = $this->getAuthService()->getCurrentUser();
            $isRight = $this->getAuthService()->checkPayPassword($user['id'], $payPassword);

            if (!$isRight) {
                throw new Exception($this->getKernel()->trans('支付密码不正确，创建订单失败!'));
            }
        }

        $coinPreferentialPrice = 0;

        if ($priceType == 'RMB') {
            $coinPreferentialPrice = $coinPayAmount / $cashRate;
        } elseif ($priceType == 'Coin') {
            $coinPreferentialPrice = $coinPayAmount;
        }

        return round($amount * 1000 - $coinPreferentialPrice * 1000) / 1000;
    }

    protected function afterCouponPay($couponCode, $targetType, $targetId, $amount, $priceType, $cashRate)
    {
        $couponResult = $this->getCouponService()->checkCouponUseable(trim($couponCode), $targetType, $targetId, $amount);

        return $couponResult;
    }

    protected function getCoinSetting()
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

    protected function calculateCoinAmount($totalPrice, $priceType, $cashRate)
    {
        $user = $this->getUserService()->getCurrentUser();

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

    protected function getCouponService()
    {
        return $this->getBiz()->service('Coupon:CouponService');
    }

    protected function getCashAccountService()
    {
        return $this->getBiz()->service('Cash:CashAccountService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }
}
