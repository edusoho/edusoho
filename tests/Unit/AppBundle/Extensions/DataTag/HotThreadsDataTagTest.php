<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\HotThreadsDataTag;

class HotThreadsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'findUsersByIds',
                'returnValue' => array(1 => array('id' => 1, 'nickname' => 'username1'), 2 => array('id' => 2, 'nickname' => 'username2')),
            ),
        ));

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('threadTime_range' => 3),
            ),
        ));

        $user = $this->getCurrentUser();

        $group1 = $this->getGroupService()->addGroup($user, array('title' => 'group1 title', 'about' => 'group about'));
        $group2 = $this->getGroupService()->addGroup($user, array('title' => 'group2 title', 'about' => 'group2 about'));

        $thread1 = $this->getThreadService()->addThread(array('title' => 'group thread1 title', 'content' => 'group thread1 content', 'userId' => 1, 'groupId' => $group1['id']));
        $post = $this->getThreadService()->postThread(array('content' => 'thread1 post title'), $group1['id'], 2, $thread1['id']);

        $thread2 = $this->getThreadService()->addThread(array('title' => 'group thread2 title', 'content' => 'group thread2 content', 'userId' => 2, 'groupId' => $group2['id']));

        $datatag = new HotThreadsDataTag();
        $result = $datatag->getData(array('count' => 5));

        $this->assertEquals(2, count($result));
        $this->assertEquals($thread1['id'], $result[0]['id']);
    }

    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }

    protected function getThreadService()
    {
        return $this->createService('Group:ThreadService');
    }
}
