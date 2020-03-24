<?php

namespace Biz\DestroyAccount\Event;

use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DestroyAccountEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.destroyed' => 'onUserDestroyed',
            'user.reject.destroy' => 'onUserRejectDestroy',
        );
    }

    public function onUserDestroyed(Event $event)
    {
        $user = $event->getSubject();

        if (!empty($user['verifiedMobile'])) {
            $site = $this->getSettingService()->get('site');
            $siteName = empty($site['name']) ? '本网校' : $site['name'];

            $smsParams = array(
                'mobiles' => $user['verifiedMobile'],
                'templateId' => 2040,
                'templateParams' => array('schoolName' => $siteName),
            );

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

        $message = array(
            'userName' => $user['nickname'],
            'reason' => $reason,
        );
        $this->getNotificationService()->notify($user['id'], 'reject-destroy', $message);

        if (!empty($user['verifiedMobile'])) {
            $smsParams = array(
                'mobiles' => $user['verifiedMobile'],
                'templateId' => 2032,
                'templateParams' => array('reason' => $reason),
            );

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

        return $biz['qiQiuYunSdk.sms'];
    }
}
