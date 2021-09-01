<?php

namespace Biz\DestroyAccount\Event;

use Biz\Sms\SmsScenes;
use Biz\Sms\SmsType;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DestroyAccountEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'user.destroyed' => 'onUserDestroyed',
            'user.reject.destroy' => 'onUserRejectDestroy',
        ];
    }

    public function onUserDestroyed(Event $event)
    {
        $user = $event->getSubject();

        if (!empty($user['verifiedMobile'])) {
            $site = $this->getSettingService()->get('site');
            $siteName = empty($site['name']) ? '本网校' : $site['name'];

            $smsParams = [
                'mobiles' => $user['verifiedMobile'],
                'templateId' => SmsType::USER_DESTROYED,
                'templateParams' => ['schoolName' => $siteName],
                'tag' => SmsScenes::USER_DESTROYED,
            ];

            try {
                $this->getSDKSmsService()->sendToOne($smsParams);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    public function onUserRejectDestroy(Event $event)
    {
        $user = $event->getSubject();
        $reason = $event->getArgument('reason');

        $message = [
            'userName' => $user['nickname'],
            'reason' => $reason,
        ];
        $this->getNotificationService()->notify($user['id'], 'reject-destroy', $message);

        if (!empty($user['verifiedMobile'])) {
            $smsParams = [
                'mobiles' => $user['verifiedMobile'],
                'templateId' => SmsType::USER_REJECT_DESTROYED,
                'templateParams' => ['reason' => $reason],
                'tag' => SmsScenes::USER_REJECT_DESTROYED,
            ];

            try {
                $this->getSDKSmsService()->sendToOne($smsParams);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return Log
     */
    protected function getLogger()
    {
        $biz = $this->getBiz();

        return $biz['logger'];
    }

    private function getSDKSmsService()
    {
        $biz = $this->getBiz();

        return $biz['ESCloudSdk.sms'];
    }
}
