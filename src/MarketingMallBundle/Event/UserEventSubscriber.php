<?php

namespace MarketingMallBundle\Event;

use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Client\MarketingMallClient;
use MarketingMallBundle\Common\GoodsContentBuilder\TeacherInfoBuilder;

class UserEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'user.change_avatar' => 'onUserAvatarChange',
            'user.update' => 'onUserProfileUpdate',
            'user.change_nickname' => 'onUserNicknameChange',
        ];
    }

    public function onUserAvatarChange(Event $event)
    {
        $user = $event->getSubject();
        $this->syncTeacherInfoToMarketingMall($user['id']);
    }

    public function onUserProfileUpdate(Event $event)
    {
        $subject = $event->getSubject();
        if (array_intersect(['title', 'about'], array_keys($subject['fields']))) {
            $this->syncTeacherInfoToMarketingMall($subject['user']['id']);
        }
    }

    public function onUserNicknameChange(Event $event)
    {
        $user = $event->getSubject();
        if ($user['nickname'] != $event->getArgument('oldNickname')) {
            $this->syncTeacherInfoToMarketingMall($user['id']);
        }
    }

    protected function syncTeacherInfoToMarketingMall($userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if (!in_array('ROLE_TEACHER', $user['roles'])) {
            return;
        }
        $builder = new TeacherInfoBuilder($this->getBiz());
        $client = new MarketingMallClient($this->getBiz());
        $client->updateGoodsContent([
            'type' => 'teacher',
            'body' => $builder->build($userId),
        ]);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
