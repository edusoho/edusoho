<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Strategy;

use Biz\User\Service\UserService;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractNotificationSendStrategy
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function filterLockUser($userIds)
    {
        if (empty($userIds)) {
            return [];
        }
        $users = $this->getUserService()->searchUsers(
            ['userIds' => $userIds, 'locked' => 0],
            [],
            0,
            count($userIds)
        );

        return array_column($users, 'id');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->biz->service('WeChat:WeChatService');
    }
}
