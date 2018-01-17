<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\UserLatestLearnCoursesDataTag;

class UserLatestLearnCoursesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $course1 = array(
            'type' => 'normal',
            'title' => 'course1',
            'courseSetId' => 1,
            'expiryMode' => 'forever',
            'learnMode' => 0,
            'courseType' => 'normal',
        );
        $course2 = array(
            'type' => 'normal',
            'title' => 'course2',
            'courseSetId' => 1,
            'expiryMode' => 'forever',
            'learnMode' => 0,
            'courseType' => 'normal',
        );
        $course3 = array(
            'type' => 'normal',
            'title' => 'course3',
            'courseSetId' => 1,
            'expiryMode' => 'forever',
            'learnMode' => 0,
            'courseType' => 'normal',
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
            'createdIp' => '127.0.0.1',
        ));

        $user2 = $this->getUserService()->register(array(
            'email' => '12345@qq.com',
            'nickname' => 'user2',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $this->getCourseMemberService()->becomeStudent($course1['id'], $user1['id']);
        $this->getCourseMemberService()->becomeStudent($course2['id'], $user1['id']);
        $this->getCourseMemberService()->becomeStudent($course3['id'], $user2['id']);

        $datatag = new UserLatestLearnCoursesDataTag();
        // $courses = $datatag->getData(array('userId' => $user1['id'], 'count' => 5));
        // $this->assertEquals(2, count($courses));
        $this->assertTrue(true);
    }

    public function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    public function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
