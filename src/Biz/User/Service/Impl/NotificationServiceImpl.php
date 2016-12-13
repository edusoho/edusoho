<?php
namespace Biz\User\Impl;

use Biz\User\NotificationService;
use Biz\BaseService;

class NotificationServiceImpl extends BaseService implements NotificationService
{
    public function notify($userId, $type, $content)
    {
        $notification                = array();
        $notification['userId']      = $userId;
        $notification['type']        = empty($type) ? 'default' : (string) $type;
        $notification['content']     = is_array($content) ? $content : array('message' => $content);
        $notification['createdTime'] = time();
        $notification['isRead']      = 0;
        $this->getNotificationDao()->create(NotificationSerialize::serialize($notification));
        $this->getUserService()->waveUserCounter($userId, 'newNotificationNum', 1);
        return true;
    }

    public function searchNotifications($conditions, $orderBy, $start, $limit)
    {
        return NotificationSerialize::unserializes(
            $this->getNotificationDao()->search($conditions, $orderBy, $start, $limit)
        );
    }

    public function countNotifications($conditions)
    {
        return $this->getNotificationDao()->count($conditions);
    }

    public function findUserNotifications($userId, $start, $limit)
    {
        return NotificationSerialize::unserializes(
            $this->getNotificationDao()->findByUserId($userId, $start, $limit)
        );
    }

    public function countNotifications($userId)
    {
        return $this->getNotificationDao()->countByUserId($userId);
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

class NotificationSerialize
{
    public static function serialize(array $notification)
    {
        $notification['content'] = json_encode($notification['content']);
        return $notification;
    }

    public static function unserialize(array $notification = null)
    {
        if (empty($notification)) {
            return null;
        }
        $notification['content'] = json_decode($notification['content'], true);
        return $notification;
    }

    public static function unserializes(array $notifications)
    {
        return array_map(function ($notification) {
            return NotificationSerialize::unserialize($notification);
        }, $notifications);
    }
}
