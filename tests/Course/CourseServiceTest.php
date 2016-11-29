<?php

namespace Tests;

use Topxia\Service\Common\BaseTestCase;

class CourseServiceTest extends BaseTestCase
{
    public function testFindCoursesByCourseSetId()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'byOrder',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);
        $courses = $this->getCourseService()->findCoursesByCourseSetId(1);
        $this->assertEquals(sizeof($courses), 1);
    }

    public function testCreateAndGet()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'byOrder',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);

        $created = $this->getCourseService()->getCourse($result['id']);
        $this->assertEquals($result['title'], $created['title']);
    }

    public function testUpdate()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'byOrder',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);

        $result['title'] = '第一个教学计划(改)';
        unset($result['learnMode']);

        $updated = $this->getCourseService()->updateCourse($result['id'], $result);

        $this->assertEquals($updated['title'], $result['title']);
    }

    public function testDelete()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'byOrder',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);

        $deleted = $this->getCourseService()->deleteCourse($result['id']);

        $this->assertEquals($deleted, 1);
    }

    public function testCloseCourse()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'byOrder',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($result['id'], 1);
        $this->getCourseService()->closeCourse($result['id']);

        $closed = $this->getCourseService()->getCourse($result['id']);

        $this->assertTrue($closed['status'] == 'closed');
    }

    public function testPublishCourse()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'byOrder',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->publishCourse($result['id'], 1);

        $published = $this->getCourseService()->getCourse($result['id']);
        $this->assertEquals($published['status'], 'published');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
