<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestLoginUsersDataTag;

class LatestLoginUsersDataTagTest extends BaseTestCase
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
        $datatag = new LatestLoginUsersDataTag();
        $users = $datatag->getData(array('count' => 5));
        $this->assertEquals(4, count($users)); //默认有测试管理员１个
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
