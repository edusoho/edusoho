<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestTeachersDataTag;

class LatestTeachersDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountEmpty()
    {
        $dataTag = new LatestTeachersDataTag();
        $dataTag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $dataTag = new LatestTeachersDataTag();
        $dataTag->getData(array('countId' => 101));
    }

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

        $datatag = new LatestTeachersDataTag();
        $teachers = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($teachers));
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
