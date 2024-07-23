<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\HotThreadsDataTag;
use Biz\BaseTestCase;

class HotThreadsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'findUsersByIds',
                'returnValue' => [1 => ['id' => 1, 'nickname' => 'username1'], 2 => ['id' => 2, 'nickname' => 'username2']],
            ],
        ]);

        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'returnValue' => ['threadTime_range' => 3],
            ],
        ]);

        $this->mockBiz('Mall:MallService', [
            [
                'functionName' => 'isInit',
                'returnValue' => false,
            ],
        ]);

        $user = $this->getCurrentUser();

        $group1 = $this->getGroupService()->addGroup($user, ['title' => 'group1 title', 'about' => 'group about']);
        $group2 = $this->getGroupService()->addGroup($user, ['title' => 'group2 title', 'about' => 'group2 about']);

        $thread1 = $this->getThreadService()->addThread(['title' => 'group thread1 title', 'content' => 'group thread1 content', 'userId' => 1, 'groupId' => $group1['id']]);
        $post = $this->getThreadService()->postThread(['content' => 'thread1 post title'], $group1['id'], 2, $thread1['id']);

        $thread2 = $this->getThreadService()->addThread(['title' => 'group thread2 title', 'content' => 'group thread2 content', 'userId' => 2, 'groupId' => $group2['id']]);

        $datatag = new HotThreadsDataTag();
        $result = $datatag->getData(['count' => 5]);

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
