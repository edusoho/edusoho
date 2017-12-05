<?php

namespace Tests\Unit\Course\Copy;

use Biz\BaseTestCase;
use Biz\Course\Copy\CourseMemberCopy;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Service\CourseService;

class CourseMemberCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $copy = new CourseMemberCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ), false);

        $this->assertNull($copy->preCopy($this->mockCourse(), array()));
    }

    public function testDoCopy()
    {
        $course = $this->getCourseService()->createCourse($this->mockCourse());
        $user = $this->getCurrentUser();
        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'update', 'returnValue' => array()),
        ));

        $copy = new CourseMemberCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ), false);

        $copy->doCopy(array(), array('originCourse' => array('id' => $course['id'], 'courseSetId' => $course['courseSetId']), 'newCourse' => array('id' => $course['id'] + 2, 'courseSetId' => $course['id'] + 2)));

        $member = $this->getMemberDao()->getByCourseIdAndUserId($course['id'] + 2, $user->getId());

        $this->assertNotEmpty($member);
    }

    public function testAfterCopy()
    {
        $copy = new CourseMemberCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ), false);

        $this->assertNull($copy->afterCopy($this->mockCourse(), array()));
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->biz->dao('Course:CourseMemberDao');
    }
}
