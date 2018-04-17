<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TeacherCoursesDataTag;

class TeacherCoursesDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new TeacherCoursesDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new TeacherCoursesDataTag();
        $datatag->getData(array('count' => 101));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyUserId()
    {
        $dataTag = new TeacherCoursesDataTag();
        $announcement = $dataTag->getData(array('count' => 5));
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
        $this->getUserService()->changeUserRoles($user1['id'], array('ROLE_USER', 'ROLE_TEACHER'));

        $user = $this->getCurrentUser();
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $course = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));
        $this->getCourseMemberService()->setCourseTeachers($course['id'], array(array('id' => $user1['id'])));
        $this->getCourseService()->publishCourse($course['id']);

        $datatag = new TeacherCoursesDataTag();
        $courses = $courses = $datatag->getData(array('userId' => $user1['id'], 'count' => 5));

        $this->assertNotNull($courses);
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    public function getUserService()
    {
        return $this->createService('User:UserService');
    }

    public function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    public function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
