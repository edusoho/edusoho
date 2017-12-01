<?php

namespace Tests\Unit\Course\Copy\CourseSet;

use Biz\BaseTestCase;
use Biz\Course\Copy\CourseSet\CourseSetCopy;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class CourseSetCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $copy = new CourseSetCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
            'priority' => 100,
        ), false);

        $this->assertNull($copy->preCopy(array(), array()));
    }

    public function testDoCopy()
    {
        $courseSet = $this->createNewCourseSet();
        $this->createNewCourse($courseSet['id']);

        $copy = new CourseSetCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
            'isCopy' => 1,
            'priority' => 100,
        ), false);
        $newCourseSet = $copy->doCopy($courseSet, array('params' => array('title' => '复制出来的courseSet')));
        $this->assertEquals('复制出来的courseSet', $newCourseSet['newCourseSet']['title']);
    }

    protected function createNewCourse($courseSetId)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array($courseSetId));

        if (empty($courses)) {
            $courseFields = array(
                'title' => '第一个教学计划',
                'courseSetId' => 1,
                'learnMode' => 'lockMode',
                'expiryDays' => 0,
                'expiryMode' => 'forever',
            );

            $course = $this->getCourseService()->createCourse($courseFields);
        } else {
            $course = $courses[0];
        }

        $this->assertNotEmpty($course);

        return $course;
    }

    protected function createNewCourseSet()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $this->assertNotEmpty($courseSet);

        return $courseSet;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
