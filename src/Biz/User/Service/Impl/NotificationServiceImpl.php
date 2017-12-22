<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Service\NotificationService;

class NotificationServiceImpl extends BaseService implements NotificationService
{
    public function notify($userId, $type, $content)
    {
        $notification = array();
        $notification['userId'] = $userId;
        $notification['type'] = empty($type) ? 'default' : (string) $type;
        $notification['content'] = is_array($content) ? $content : array('message' => $content);
        $notification['createdTime'] = time();
        $notification['isRead'] = 0;
        $this->getNotificationDao()->create($notification);
        $this->getUserService()->waveUserCounter($userId, 'newNotificationNum', 1);

        return true;
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
        return $this->getNotificationDao()->count(array('userId' => $userId));
    }

    public function clearUserNewNotificationCounter($userId)
    {
        return $this->getUserService()->clearUserCounter($userId, 'newNotificationNum');
    }

    public function getNotificationDao()
    {
        return $this->createDao('User:NotificationDao');
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
