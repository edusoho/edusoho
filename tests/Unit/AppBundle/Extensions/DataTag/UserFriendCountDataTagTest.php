<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\UserFriendCountDataTag;

class UserFriendCountDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $user1 = $this->getUserService()->register(array(
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $user2 = $this->getUserService()->register(array(
            'email' => '12345@qq.com',
            'nickname' => 'user2',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $user3 = $this->getUserService()->register(array(
            'email' => '123456@qq.com',
            'nickname' => 'user3',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $user4 = $this->getUserService()->register(array(
            'email' => '1234567@qq.com',
            'nickname' => 'user4',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $this->getUserService()->follow($user2['id'], $user1['id']);
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user1['id'], $user4['id']);
        $dataTag = new UserFriendCountDataTag();
        $count = $dataTag->getData(array('userId' => $user1['id']));

        $this->assertEquals(2, $count['following']);
        $this->assertEquals(1, $count['follower']);
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
