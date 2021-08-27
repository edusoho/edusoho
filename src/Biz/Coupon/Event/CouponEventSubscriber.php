<?php

namespace Biz\Coupon\Event;

use AppBundle\Common\MathToolkit;
use Biz\Coupon\Service\CouponBatchService;
use Biz\Coupon\Service\CouponService;
use Biz\Sms\SmsScenes;
use Biz\Sms\SmsType;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CouponEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'coupon.use' => 'onCouponUse',
            'coupon.receive' => 'onCouponReceive',
            'coupon.append' => 'onCouponAppend',
        ];
    }

    public function onCouponUse(Event $event)
    {
        $coupon = $event->getSubject();

        if (empty($coupon['batchId'])) {
            return;
        }

        $usedCount = $this->getCouponService()->searchCouponsCount(
            ['status' => 'used', 'batchId' => $coupon['batchId']]
        );
        $allDiscount = $this->getCouponBatchService()->sumDeductAmountByBatchId($coupon['batchId']);

        $this->getCouponBatchService()->updateBatch(
            $coupon['batchId'],
            ['usedNum' => $usedCount, 'money' => MathToolkit::simple($allDiscount, 0.01)]
        );
    }

    public function onCouponAppend(Event $event)
    {
        $batch = $event->getSubject();

        $inviteSetting = $this->getSettingService()->get('invite', []);
        if (empty($inviteSetting['remain_number']) || $batch['unreceivedNum'] < $inviteSetting['remain_number']) {
            return;
        }

        if ($inviteSetting['promoted_user_batchId'] == $batch['id'] && !empty($inviteSetting['promoted_user_enable'])) {
            $isSmsSend = 'promoted_sms_send';
        } elseif ($inviteSetting['promote_user_batchId'] == $batch['id'] && !empty($inviteSetting['promote_user_enable'])) {
            $isSmsSend = 'promote_sms_send';
        }
        if (empty($isSmsSend)) {
            return;
        }
        $inviteSetting[$isSmsSend] = 0;
        $this->getSettingService()->set('invite', $inviteSetting);
    }

    public function onCouponReceive(Event $event)
    {
        $batch = $event->getSubject();
        $batch = $this->getCouponBatchService()->getBatch($batch['id']);

        $smsSetting = $this->getSettingService()->get('cloud_sms', []);
        if (empty($smsSetting['sms_enabled'])) {
            return;
        }

        $inviteSetting = $this->getSettingService()->get('invite', []);
        if (empty($inviteSetting['invite_code_setting']) || empty($inviteSetting['mobile'])) {
            return;
        }

        if ($inviteSetting['promoted_user_batchId'] == $batch['id'] && !empty($inviteSetting['promoted_user_enable'])) {
            $isSmsSend = 'promoted_sms_send';
        } elseif ($inviteSetting['promote_user_batchId'] == $batch['id'] && !empty($inviteSetting['promote_user_enable'])) {
            $isSmsSend = 'promote_sms_send';
        }
        if (empty($isSmsSend) || ($inviteSetting[$isSmsSend] && 0 != $batch['unreceivedNum'])) {
            return;
        }

        if (0 == $batch['unreceivedNum']) {
            $inviteSetting['invite_code_setting'] = 0;
            $this->getSettingService()->set('invite', $inviteSetting);
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
        $inviteSetting = $this->getSettingService()->get('invite', []);

        $templateParams = [
            'activity_name' => '邀请注册',
            'reward_name' => $batch['name'],
        ];

        if (0 != $batch['unreceivedNum']) {
            $templateParams['remain'] = $batch['unreceivedNum'];
        }

        $smsParams = [
            'mobiles' => $inviteSetting['mobile'],
            'templateId' => $type,
            'templateParams' => $templateParams,
        ];

        if (SmsType::INVITE_REWARD_INSUFFICIENT == $type) {
            $smsParams['tag'] = SmsScenes::INSUFFICIENT_REGISTER_REWARD;
        }

        if (SmsType::INVITE_REWARD_EXHAUST == $type) {
            $smsParams['tag'] = SmsScenes::CONSUMED_REGISTER_REWARD;
        }

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

        return $biz['ESCloudSdk.sms'];
    }
}
