<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\RecommendTeachersDataTag;

class RecommendTeachersDataTagTest extends BaseTestCase
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
        $this->getUserService()->promoteUser($user2['id']);
        $this->getUserService()->promoteUser($user3['id']);
        $datatag = new RecommendTeachersDataTag();
        $teacher = $datatag->getData(array('count' => 5, 'userId' => $user1['id']));
        $this->assertEquals(2, count($teacher));
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
