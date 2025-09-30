<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Dao\NotificationDao;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;

class NotificationServiceImpl extends BaseService implements NotificationService
{
    public function notify($userId, $type, $content)
    {
        $notification = [];
        $notification['userId'] = $userId;
        $notification['type'] = empty($type) ? 'default' : (string) $type;
        $notification['content'] = is_array($content) ? $content : ['message' => $content];
        $notification['createdTime'] = time();
        $notification['isRead'] = 0;
        $this->getNotificationDao()->create($notification);
        $this->getUserService()->waveUserCounter($userId, 'newNotificationNum', 1);

        return true;
    }

    public function batchNotify($userIds, $type, $content)
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'userId' => $userId,
                'type' => empty($type) ? 'default' : $type,
                'content' => is_array($content) ? $content : ['message' => $content],
                'isRead' => 0,
            ];
        }
        $this->getNotificationDao()->batchCreate($notifications);
        $this->getUserService()->waveUsersCounter($userIds, 'newNotificationNum', 1);
    }

    public function isSelectNotification($notifications, $id)
    {
        foreach ($notifications as &$notification) {
            if ($notification['id'] == $id) {
                $notification['highLight'] = 1;
            }
        }

        return $notifications;
    }

    public function findBatchIdsByUserIdAndType($userId, $type)
    {
        return $this->getNotificationDao()->findBatchIdsByUserIdAndType($userId, $type);
    }

    public function searchNotifications($conditions, $orderBy, $start, $limit)
    {
        return $this->getNotificationDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countNotifications($conditions)
    {
        return $this->getNotificationDao()->count($conditions);
    }

    public function searchNotificationsByUserId($userId, $start, $limit)
    {
        return $this->getNotificationDao()->searchByUserId($userId, $start, $limit);
    }

    public function countNotificationsByUserId($userId)
    {
        return $this->getNotificationDao()->count(['userId' => $userId]);
    }

    public function clearUserNewNotificationCounter($userId)
    {
        return $this->getUserService()->clearUserCounter($userId, 'newNotificationNum');
    }

    /**
     * @return NotificationDao
     */
    public function getNotificationDao()
    {
        return $this->createDao('User:NotificationDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
