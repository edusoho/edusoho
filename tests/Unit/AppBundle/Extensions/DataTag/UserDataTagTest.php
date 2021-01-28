<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\UserDataTag;

class UserDataTagTest extends BaseTestCase
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
        $datatag = new UserDataTag();
        $user = $datatag->getData(array('userId' => $user1['id']));
        $this->assertEquals($user['id'], $user1['id']);
        $this->assertEquals($user['email'], $user1['email']);
        $this->assertEquals($user['nickname'], $user1['nickname']);
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
