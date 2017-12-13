<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PromotedTeacherDataTag;

class PromotedTeacherDataTagTest extends BaseTestCase
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
        $this->getUserService()->changeUserRoles($user1['id'], array('ROLE_USER', 'ROLE_TEACHER'));
        $this->getUserService()->changeUserRoles($user2['id'], array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN'));
        $this->getUserService()->changeUserRoles($user3['id'], array('ROLE_USER'));
        $this->getUserService()->promoteUser($user1['id']);
        sleep(2);
        $this->getUserService()->promoteUser($user2['id']);
        $this->getUserService()->promoteUser($user3['id']);
        $datatag = new PromotedTeacherDataTag();
        $teacher = $datatag->getData(array());
        $this->assertEquals($user2['id'], $teacher['id']);
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
