<?php

namespace Tests\Unit\Order;

use Biz\Course\Service\CourseSetService;
use Biz\BaseTestCase;
use Biz\Order\OrderProcessor\CourseOrderProcessor;

class CourseOrderProcessorTest extends BaseTestCase
{
    public function testIsMemberPreCheck()
    {
        $course = $this->mockCourse(array('title' => 'course 1'));
        $this->getCourseService()->publishCourse($course['id'], $this->getCurrentUser()->getId());

        $student = $this->getUserService()->register(
            array(
                'nickname' => 'student',
                'email' => 'student@student.com',
                'password' => 'student',
                'createdIp' => '127.0.0.1',
                'orgCode' => '1.',
                'orgId' => '1',
            )
        );

        $this->getMemberService()->becomeStudent($course['id'], $student['id']);

        $result = $this->getCourseOrderProcessor()->preCheck($course['id'], $student['id']);

        $this->assertArrayHasKey('error', $result);
    }

    public function testPreCheck()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);
        $course = $this->mockCourse(array('title' => 'course 1', 'courseSetId' => $courseSet['id']));

        $this->getCourseService()->publishCourse($course['id'], $this->getCurrentUser()->getId());
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);
        $result = $this->getCourseOrderProcessor()->preCheck($course['id'], $this->getCurrentUser()->getId());
        $this->assertTrue(empty($result['error']));
    }

    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    protected function getDefaultMockFields()
    {
        return array(
            'title' => 'course',
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'expiryStartDate' => '',
            'expiryEndDate' => '',
            'courseType' => 'normal',
        );
    }

    protected function mockCourse($fields)
    {
        $fields = array_merge($this->getDefaultMockFields(), $fields);

        return $this->getCourseService()->createCourse($fields);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseOrderProcessor()
    {
        return new CourseOrderProcessor($this->getBiz());
    }
}
