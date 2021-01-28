<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestGroupThreadsDataTag;

class LatestGroupThreadsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $user1 = $this->getuserService()->register(array(
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));

        $group1 = $this->getGroupService()->addGroup($user1, array(
            'title' => 'group1',
            'about' => 'group1',
            'ownerId' => $user1['id'],
            'memberNum' => 1,
            'createdTime' => '',
        ));
        $group2 = $this->getGroupService()->addGroup($user1, array(
            'title' => 'group2',
            'about' => 'group2',
            'ownerId' => $user1['id'],
            'memberNum' => 1,
            'createdTime' => '',
        ));

        $thread1 = $this->getThreadService()->addThread(array(
            'title' => 'thread1',
            'content' => 'content1',
            'groupId' => $group1['id'],
            'createdTime' => '',
            'userId' => $user1['id'],
        ));
        $thread2 = $this->getThreadService()->addThread(array(
            'title' => 'thread2',
            'content' => 'content2',
            'groupId' => $group2['id'],
            'createdTime' => '',
            'userId' => $user1['id'],
        ));
        $thread3 = $this->getThreadService()->addThread(array(
            'title' => 'thread3',
            'content' => 'content3',
            'groupId' => $group1['id'],
            'createdTime' => '',
            'userId' => $user1['id'],
        ));
        $datatag = new LatestGroupThreadsDataTag();

        $threads = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($threads));
    }

    private function getThreadService()
    {
        return $this->createService('Group:ThreadService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    private function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }
}
