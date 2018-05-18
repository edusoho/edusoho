<?php

namespace Tests\Unit\User\Service;

use Biz\BaseTestCase;

class NotificationServiceTest extends BaseTestCase
{
    public function testAddNotification()
    {
        $user = $this->createUser();
        $this->getNotificationService()->notify($user['id'], 'default', 'content');

        $notificationsNum = $notifications = $this->getNotificationService()->countNotificationsByUserId($user['id']);
        $this->assertEquals(1, $notificationsNum);
    }

    public function testListUserNotifications()
    {
        $user = $this->createUser();
        $this->getNotificationService()->notify($user['id'], 'default', 'content');
        $this->getNotificationService()->notify($user['id'], 'default', 'content');

        $notificationsNum = $notifications = $this->getNotificationService()->countNotificationsByUserId($user['id']);
        $this->assertEquals(2, $notificationsNum);

        $notifications = $this->getNotificationService()->searchNotificationsByUserId($user['id'], 0, 30);

        $this->assertEquals($user['id'], $notifications[0]['userId']);
        $this->assertEquals('default', $notifications[0]['type']);
        $this->assertEquals(0, $notifications[0]['isRead']);
        $this->assertEquals('content', $notifications[0]['content']['message']);

        $this->assertEquals($user['id'], $notifications[1]['userId']);
        $this->assertEquals('default', $notifications[1]['type']);
        $this->assertEquals(0, $notifications[1]['isRead']);
        $this->assertEquals('content', $notifications[1]['content']['message']);
    }

    public function testIsSelectNotification()
    {
        $notifications = array(array('id' => 2, 'content' => 'test'));
        $result = $this->getNotificationService()->isSelectNotification($notifications, 2);
        $this->assertEquals(1, $result[0]['highLight']);
    }

    protected function createUser()
    {
        $user = array();
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user';

        return $this->getUserService()->register($user);
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }
}
