<?php

namespace Tests\Unit\Course\Copy;

use Biz\BaseTestCase;
use Biz\Course\Copy\CourseMemberCopy;

class CourseMemberCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $copy = new CourseMemberCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ),false);

        $this->assertNull($copy->preCopy($this->mockCourse(), array()));
    }

    public function testDoCopy()
    {
        $courseMembers = array(
            array(
                'courseId' => 1,
                'courseSetId' => 1,
                'userId' => 1,
                'role' => 'teacher',
                'seq' => 1,
                'isVisible' => 1,
                'createdTime' => time(),
            )
        );
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findByCourseIdAndRole', 'returnValue' => $courseMembers)
        ));

        $this->

        $copy = new CourseMemberCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ),false);
    }

    public function testAfterCopy()
    {
        $copy = new CourseMemberCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ),false);

        $this->assertNull($copy->afterCopy($this->mockCourse(), array()));
    }

    protected function mockCopyCourses()
    {
    
    }

    protected function mockCourse($title = '测试课程', $courseSet = array())
    {
        return array(
            'title' => $title,
            'courseSetId' => empty($courseSet) ? 1 : $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'courseType' => 'normal',
        );
    }
}