<?php

namespace Biz\Coupon\Event;

use Biz\Coupon\Service\CouponService;
use Biz\Order\Service\OrderService;
use Biz\Sms\SmsType;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Biz\Coupon\Service\CouponBatchService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Common\MathToolkit;

class CouponEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'coupon.use' => 'onCouponUse',
            'coupon.receive' => 'onCouponReceive',
        );
    }

    public function onCouponUse(Event $event)
    {
        $coupon = $event->getSubject();

        if (empty($coupon['batchId'])) {
            return;
        }

        $usedCount = $this->getCouponService()->searchCouponsCount(
            array('status' => 'used', 'batchId' => $coupon['batchId'])
        );
        $allDiscount = $this->getCouponBatchService()->sumDeductAmountByBatchId($coupon['batchId']);

        $this->getCouponBatchService()->updateBatch(
            $coupon['batchId'],
            array('usedNum' => $usedCount, 'money' => MathToolkit::simple($allDiscount, 0.01))
        );
    }

    public function onCouponReceive(Event $event)
    {
        $batch = $event->getSubject();

        $inviteSetting = $this->getSettingService()->get('invite', array());

        if ($inviteSetting['promoted_user_batchId'] != $batch['id'] && $inviteSetting['promote_user_batchId'] != $batch['id']) {
            return;
        }

        if ($inviteSetting['promoted_user_batchId'] == $batch['id']) {
            $isSmsSend = 'promoted_sms_send';
        } else {
            $isSmsSend = 'promote_sms_send';
        }

        if ($inviteSetting[$isSmsSend]) {
            return;
        }

        if (0 == $batch['unreceivedNum']) {
            $this->sendSms(SmsType::INVITE_REWARD_EXHAUST, $batch);

            return;
        }

        if ($batch['unreceivedNum'] < $inviteSetting['remain_number']) {
            $this->sendSms(SmsType::INVITE_REWARD_INSUFFICIENT, $batch);

            $inviteSetting[$isSmsSend] = 1;
            $this->getSettingService()->set('invite', $inviteSetting);
        }
    }

    private function sendSms($type, $batch)
    {
        $inviteSetting = $this->getSettingService()->get('invite', array());

        $templateParams = array(
            'name' => $batch['name'],
            'remain' => $inviteSetting['remain_number'],
        );

        $smsParams = array(
            'mobiles' => $inviteSetting['mobile'],
            'templateId' => $type,
            'templateParams' => $templateParams,
        );

        try {
            $this->getSDKSmsService()->sendToOne($smsParams);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->getBiz()->service('Coupon:CouponService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->getBiz()->service('Coupon:CouponBatchService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    private function getSDKSmsService()
    {
        $biz = $this->getBiz();

        return $biz['qiQiuYunSdk.sms'];
    }
}
