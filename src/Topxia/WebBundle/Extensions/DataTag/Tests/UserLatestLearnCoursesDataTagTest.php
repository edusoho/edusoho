<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\UserLatestLearnCoursesDataTag;

class UserLatestLearnCoursesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
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
    	$this->getCourseService()->becomeStudent($course1['id'],$user1['id']);
    	$this->getCourseService()->becomeStudent($course2['id'],$user1['id']);
    	$this->getCourseService()->becomeStudent($course3['id'],$user2['id']);

        $datatag = new UserLatestLearnCoursesDataTag();
        $courses = $datatag->getData(array('userId' => $user1['id'], 'count' => 5));
        $this->assertEquals(2,count($courses));

    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    public function getCourseService()
    {
    	return $this->getServiceKernel()->createService('Course.CourseService');
    }
}