<?php

namespace Tests\Unit\User\Service;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class BatchNotificationServiceTest extends BaseTestCase
{
    public function testsendBatchNotification()
    {
        $fields = array(
            'type' => 'text',
            'fromId' => 1,
            'title' => 'asmd',
            'content' => 'sdncsdn',
            'targetType' => 'global',
            'targetId' => 0,
            'createdTime' => 0,
            'sendedTime' => 0,
            'published' => 0,
        );
        $notification = $this->getBatchNotificationService()->createBatchNotification($fields);
        $this->getBatchNotificationService()->createBatchNotification($fields);

        $notification1 = $this->getBatchNotificationService()->getBatchNotification(1);
        $notification2 = $this->getBatchNotificationService()->getBatchNotification(2);

        $conditions = array('fromId' => 1);
        $num = $this->getBatchNotificationService()->countBatchNotifications($conditions);

        $notifications = $this->getBatchNotificationService()->searchBatchNotifications($conditions, array('createdTime' => 'ASC'), 0, 9999);

        $user = $this->createUser();
        $result = $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);
        $this->getBatchNotificationService()->deleteBatchNotification(1);
        $notification2 = $this->getBatchNotificationService()->getBatchNotification(1);
        $notification3 = $this->getBatchNotificationService()->getBatchNotification(2);

        $notification3['content'] = empty($notification3['content']) ? 'aaaaaa' : 'bbbbbb';
        $this->getBatchNotificationService()->updateBatchNotification(2, $notification3);
    }

    public function testPublishBatchNotification()
    {
        $fields = array(
            'type' => 'text',
            'fromId' => 1,
            'title' => 'asmd',
            'content' => 'sdncsdn',
            'targetType' => 'global',
            'targetId' => 0,
            'createdTime' => 0,
            'sendedTime' => 0,
            'published' => 0,
        );
        $notification = $this->getBatchNotificationService()->createBatchNotification($fields);
        $result = $this->getBatchNotificationService()->publishBatchNotification(1);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testPublishBatchNotificationWithEmptyNotification()
    {
        $this->mockBiz(
            'User:BatchNotificationDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array(2),
                ),
            )
        );
        $this->getBatchNotificationService()->publishBatchNotification(2);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testPublishBatchNotificationWithPublishedNotification()
    {
        $this->mockBiz(
            'User:BatchNotificationDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 2, 'published' => 1),
                    'withParams' => array(2),
                ),
            )
        );
        $this->getBatchNotificationService()->publishBatchNotification(2);
    }

    public function testCheckoutBatchNotification()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 2, 'createdTime' => 322222),
                    'withParams' => array(2),
                ),
                array(
                    'functionName' => 'waveUserCounter',
                    'withParams' => array(2, 'newNotificationNum', 1),
                ),
            )
        );
        $this->mockBiz(
            'User:NotificationService',
            array(
                array(
                    'functionName' => 'findBatchIdsByUserIdAndType',
                    'returnValue' => array(array('id' => 2, 'batchId' => 2)),
                    'withParams' => array(2, 'global'),
                ),
            )
        );
        $fields = array(
            'type' => 'text',
            'fromId' => 1,
            'title' => 'asmd',
            'content' => 'sdncsdn',
            'targetType' => 'global',
            'targetId' => 0,
            'createdTime' => 0,
            'sendedTime' => time() + 5000,
            'published' => 1,
        );
        $notification = $this->getBatchNotificationService()->createBatchNotification($fields);
        $result = $this->getBatchNotificationService()->checkoutBatchNotification(2);
        $this->assertNull($result);
    }

    public function testAddBatchNotification()
    {
        $this->mockBiz(
            'User:BatchNotificationDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array('id' => 2, 'title' => 'title'),
                    'withParams' => array(array(
                        'type' => 'text',
                        'title' => 'title',
                        'fromId' => 2,
                        'content' => 'sdncsdn',
                        'targetType' => 'global',
                        'targetId' => 0,
                        'createdTime' => 0,
                        'sendedTime' => 0,
                        'published' => 1,
                    )),
                ),
            )
        );
        $fields = array('text', 'title', 2, 'sdncsdn', 'global', 0, 0, 0, 1);
        $service = $this->getBatchNotificationService();
        $result = ReflectionUtils::invokeMethod($service, 'addBatchNotification', $fields);
        $this->assertEquals(array('id' => 2, 'title' => 'title'), $result);
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

    protected function getBatchNotificationService()
    {
        return $this->createService('User:BatchNotificationService');
    }
}
