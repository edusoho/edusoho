<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\PersonDynamicDataTag;

class PersonDynamicDataTagTest extends BaseTestCase
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
    	$user2 = $this->getUserService()->register(array(
            'email' => '12345@qq.com',
            'nickname' => 'user2',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1'
        ));
        $course1 = array(
            'type' => 'normal',
            'title' => 'course1'
        );
        $course2 = array(
            'type' => 'normal',
            'title' => 'course2'
        );
        $course3 = array(
            'type' => 'normal',
            'title' => 'course3'
        );

        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $course3 = $this->getCourseService()->createCourse($course3);
        $this->getCourseService()->publishCourse($course1['id']);
        $this->getCourseService()->publishCourse($course2['id']);
        $this->getCourseService()->publishCourse($course3['id']);
        $this->getCourseService()->becomeStudent($course1['id'],$user1['id']);
    	$this->getCourseService()->becomeStudent($course2['id'],$user1['id']);
    	$this->getCourseService()->becomeStudent($course3['id'],$user2['id']);
        $datatag = new PersonDynamicDataTag();
        $status = $datatag->getData(array('count' => 5));
        $this->assertEquals(3,count($status));

    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    private function getStatusService() 
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }
    public function getCourseService()
    {
    	return $this->getServiceKernel()->createService('Course.CourseService');
    }

}