<?php

namespace Tests\User;

use Biz\BaseTestCase;;

class BatchNotificationServiceTest extends BaseTestCase
{
    public function testsendBatchNotification()
    {
        $fields = array(
            'type'        => 'text',
            'fromId'      => 1,
            'title'       => 'asmd',
            'content'     => 'sdncsdn',
            'targetType'  => 'global',
            'targetId'    => 0,
            'createdTime' => 0,
            'sendedTime'  => 0,
            'published'   => 0
        );
        $notification = $this->getBatchNotificationService()->createBatchNotification($fields);
        $this->getBatchNotificationService()->createBatchNotification($fields);

        $notification1 = $this->getBatchNotificationService()->getBatchNotification(1);
        $notification2 = $this->getBatchNotificationService()->getBatchNotification(2);

        $conditions = array('fromId' => 1);
        $num        = $this->getBatchNotificationService()->countBatchNotifications($conditions);

        $notifications = $this->getBatchNotificationService()->searchBatchNotifications($conditions, array('createdTime' => 'ASC'), 0, 9999);

        $user   = $this->createUser();
        $result = $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);
        $this->getBatchNotificationService()->deleteBatchNotification(1);
        $notification2 = $this->getBatchNotificationService()->getBatchNotification(1);
        $notification3 = $this->getBatchNotificationService()->getBatchNotification(2);

        $notification3['content'] = empty($notification3['content']) ? 'aaaaaa' : 'bbbbbb';
        $this->getBatchNotificationService()->updateBatchNotification(2, $notification3);
    }

    protected function createUser()
    {
        $user             = array();
        $user['email']    = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        return $this->getUserService()->register($user);
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getBatchNotificationService()
    {
        return $this->createService('User:BatchNotificationService');
    }
}
