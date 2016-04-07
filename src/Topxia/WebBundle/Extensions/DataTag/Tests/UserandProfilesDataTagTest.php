<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\UserandProfilesDataTag;

class UserandProfilesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
    	$user1 = $this->getUserService()->register(array(
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1'
        ));
        $this->getUserService()->updateUserProfile($user1['id'],array('truename' => '乐山乐水'));
        $datatag = new UserandProfilesDataTag();
        $user = $datatag->getData(array('userId' => $user1['id']));
        $this->assertEquals($user['id'],$user1['id']);
        $this->assertEquals($user['profiles']['truename'],'乐山乐水');

    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}