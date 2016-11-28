<?php

namespace Tests;

use Topxia\Service\Common\BaseTestCase;

class CourseSetServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(sizeof($courses) === 1);
        $this->assertTrue($courses[0]['isDefault'] == 1);
    }

    public function testUpdate()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);

        $created['title']         = '新课程开始(更新)！';
        $created['subtitle']      = '新课程的副标题参见！';
        $created['tags']          = 'new';
        $created['categoryId']    = 6;
        $created['serializeMode'] = 'none';
        $updated                  = $this->getCourseSetService()->updateCourseSet($created['id'], $created);
        $this->assertEquals($created['title'], $updated['title']);
    }

    public function testDelete()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertNotEmpty($created);
        $this->getCourseSetService()->deleteCourseSet($created['id']);
        $deleted = $this->getCourseSetService()->getCourseSet($created['id']);
        $this->assertEmpty($deleted);
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
