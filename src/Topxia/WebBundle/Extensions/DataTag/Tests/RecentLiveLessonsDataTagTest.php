<?php


namespace Topxia\WebBundle\Extensions\DataTag\Test;


use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\RecentLiveLessonsDataTag;

class RecentLiveLessonsDataTagTest extends BaseTestCase {
    public function testGetData()
    {
        $this->getSettingService()->set('course',array('live_course_enabled' => 1));
        $course1 = array(
            'type' => 'live',
            'title' => 'course1'
        );
        $course2 = array(
            'type' => 'live',
            'title' => 'course2'
        );
        $course3 = array(
            'type' => 'live',
            'title' => 'course3'
        );

        $user1 = $this->getUserService()->register(array(
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1'
        ));

        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $course3 = $this->getCourseService()->createCourse($course3);

        $this->getCourseService()->publishCourse($course1['id']);
        $this->getCourseService()->publishCourse($course2['id']);
        $this->getCourseService()->publishCourse($course3['id']);
        $lesson1 =array(
            'courseId' => $course1['id'],
            'title' => 'lesson1',
            'type' => 'live',
            'startTime' => 1437667200,
            'length' => 200000
        );

        $lesson2 =array(
            'courseId' => $course2['id'],
            'title' => 'lesson2',
            'type' => 'live',
            'startTime' => 1437667200,
            'length' => 200000
        );

        $lesson3 =array(
            'courseId' => $course3['id'],
            'title' => 'lesson3',
            'type' => 'live',
            'startTime' => 1437667200,
            'length' => 20000000
        );

        $lesson1 = $this->getCourseService()->createLesson($lesson1);
        $lesson2 = $this->getCourseService()->createLesson($lesson2);
        $lesson3 = $this->getCourseService()->createLesson($lesson3);

        $this->getCourseService()->publishLesson($course1['id'],$lesson1['id']);
        $this->getCourseService()->publishLesson($course2['id'],$lesson2['id']);
        $this->getCourseService()->publishLesson($course3['id'],$lesson3['id']);

        $this->getCourseService()->becomeStudent($course1['id'],$user1['id']);

        $datatag = new RecentLiveLessonsDataTag();
        $lessons = $datatag->getData(array('count' => 2 , 'userId' => $user1['id']));
        $this->assertEquals('1',count($lessons));

        $lessons = $datatag->getData(array('count' => 2 ));
        $this->assertEquals('2',count($lessons));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
