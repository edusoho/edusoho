<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Service\UserAuthNotificationService;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserAuthNotificationServiceImpl extends BaseService implements UserAuthNotificationService
{
    public function notifyMallAuthSettingUpdate($auth)
    {
        $this->notifyUserProtocolUpdate($auth);
    }

    protected function notifyUserProtocolUpdate($auth)
    {
        $this->dispatch('user.Auth.setting.update', [
            'userTerms' => $auth['user_terms'],
            'userTermsBody' => $auth['user_terms_body'],
            'privacyPolicy' => $auth['privacy_policy'],
            'privacyPolicyBody' => $auth['privacy_policy_body'],
        ]);
    }

    protected function dispatchEvent($eventName, $subject, $arguments = [])
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }
}
