<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\MemberRecentlyLearnedDataTag;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;

class MemberRecentlyLearnedDataTagTest extends BaseTestCase
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

    	$lesson1 =array(
        	'courseId' => $course1['id'],
        	'title' => 'lesson1',
        	'type' => 'text'
        );

        $lesson2 =array(
        	'courseId' => $course1['id'],
        	'title' => 'lesson2',
        	'type' => 'text'
        );
        $lesson3 =array(
        	'courseId' => $course2['id'],
        	'title' => 'lesson3',
        	'type' => 'text'
        );


        $lesson1 = $this->getCourseService()->createLesson($lesson1);
        $lesson2 = $this->getCourseService()->createLesson($lesson2);        
        $lesson3 = $this->getCourseService()->createLesson($lesson3);

        $this->getCourseService()->publishLesson($course1['id'],$lesson1['id']);
        $this->getCourseService()->publishLesson($course1['id'],$lesson2['id']);
        $this->getCourseService()->publishLesson($course2['id'],$lesson3['id']);

    	$user = new CurrentUser();
    	$user1['currentIp'] = '127.0.0.1';
    	$user->fromArray($user1);
    	$this->getServiceKernel()->setCurrentUser($user);
    	
        $this->getCourseService()->startLearnLesson($course1['id'],$lesson1['id']);
        $this->getCourseService()->startLearnLesson($course1['id'],$lesson2['id']);
        
        $datatag = new MemberRecentlyLearnedDataTag();
        $courses = $datatag->getData(array('user'=>$user));
        $this->assertEquals($course1['id'],$courses['id']);

        $this->getCourseService()->startLearnLesson($course2['id'],$lesson3['id']);
        $courses = $datatag->getData(array('user'=>$user));
        $this->assertEquals($course2['id'],$courses['id']);

    }
    public function getCourseService()
    {
    	return $this->getServiceKernel()->createService('Course.CourseService');
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}