<?php

namespace Biz\Order\Service\Impl;

use AppBundle\Common\NumberToolkit;
use Biz\BaseService;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
use Biz\Order\Service\OrderFacadeService;
use AppBundle\Common\JoinPointToolkit;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class OrderFacadeServiceImpl extends BaseService implements OrderFacadeService
{
    public function getOrderInfo($targetType, $targetId, $fields)
    {
        $orderTypes = JoinPointToolkit::load('order');
        if (empty($targetType)
            || empty($targetId)
            || !array_key_exists($targetType, $orderTypes)
        ) {
            throw $this->createServiceException('参数不正确');
        }

        $currentUser = $this->getCurrentUser();
        $processor = OrderProcessorFactory::create($targetType);
        $checkInfo = $processor->preCheck($targetId, $currentUser['id']);

        if (isset($checkInfo['error'])) {
            return array($checkInfo, null, null);
        }

        $orderInfo = $processor->getOrderInfo($targetId, $fields);

        $verifiedMobile = '';

        if ((isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile']) > 0)) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        $orderInfo['verifiedMobile'] = $verifiedMobile;
        $orderInfo['hasPassword'] = strlen($currentUser['password']) > 0;

        return array(null, $orderInfo, $processor);
    }

    public function createOrder($targetType, $targetId, $fields)
    {
        try {
            if (!array_key_exists('targetId', $fields) || !array_key_exists('targetType', $fields)) {
                throw $this->createServiceException('参数不正确');
            }

            if (!empty($fields['coinPayAmount']) && $fields['coinPayAmount'] < 0) {
                throw $this->createServiceException('参数不正确');
            }

            if (!empty($fields['coinPayAmount'])
                && !$this->canUseCoinPay($fields['coinPayAmount'], $this->getCurrentUser()->getId())) {
                throw $this->createServiceException('当前使用的账户金额大于账户余额', 2001);
            }

            if (!empty($fields['coinPayAmount'])
             && (empty($fields['payPassword']) || !$this->isCorrectPayPassword($fields['payPassword'], $this->getCurrentUser()->getId()))) {
                throw new ServiceException('支付密码不正确', 2002);
            }

            $priceType = 'RMB';
            $coinSetting = $this->getSettingService()->get('coin');
            $coinEnabled = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'];

            if ($coinEnabled && isset($coinSetting['price_type'])) {
                $priceType = $coinSetting['price_type'];
            }

            $cashRate = 1;

            if ($coinEnabled && isset($coinSetting['cash_rate'])) {
                $cashRate = $coinSetting['cash_rate'];
            }

            $processor = OrderProcessorFactory::create($targetType);
            list($amount, $totalPrice, $couponResult) =
                $processor->shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);

            $amount = (string) ((float) $amount);

            //虚拟币抵扣率比较
            $target = $processor->getTarget($targetId);

            $maxRate = $coinSetting['cash_model']
                == 'deduction' && isset($target['maxRate']) ? $target['maxRate'] : 100;

            $priceCoin = $priceType == 'RMB' ? NumberToolkit::roundUp($totalPrice * $cashRate) : $totalPrice;

            if ($coinEnabled && isset($fields['coinPayAmount']) && ((int) ((float) $fields['coinPayAmount'] * $maxRate) > (int) ($priceCoin * $maxRate))) {
                throw $this->createServiceException('虚拟币抵扣超出限定，不能创建订单!');
            }

            if (isset($couponResult['useable']) && $couponResult['useable'] == 'yes') {
                $coupon = $fields['couponCode'];
                $couponDiscount = $couponResult['decreaseAmount'];
            }

            $orderFileds = array(
                'priceType' => $priceType,
                'totalPrice' => $totalPrice,
                'amount' => $amount,
                'coinRate' => $cashRate,
                'coinAmount' => empty($fields['coinPayAmount']) ? 0 : $fields['coinPayAmount'],
                'userId' => $this->getCurrentUser()->getId(),
                'payment' => 'none',
                'targetId' => $targetId,
                'coupon' => empty($coupon) ? '' : $coupon,
                'couponDiscount' => empty($couponDiscount) ? 0 : $couponDiscount,
            );

            $order = $processor->createOrder($orderFileds, $fields);

            return array($order, $processor);
        } catch (\Exception $e) {
            throw $this->createServiceException($e->getMessage(), $e->getCode());
        }
    }

    private function canUseCoinPay($coinPayAmount, $userId)
    {
        $cashAccount = $this->getCashAccountService()->getAccountByUserId($userId, true);
        $remainCash = empty($cashAccount['cash']) ? 0 : $cashAccount['cash'];

        return $remainCash >= $coinPayAmount;
    }

    private function isCorrectPayPassword($payPassword, $userId)
    {
        return $this->getAuthService()->checkPayPassword($userId, $payPassword);
    }

    private function getCashAccountService()
    {
        return $this->createService('Cash:CashAccountService');
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getAuthService()
    {
        return $this->createService('User:AuthService');
    }
}
