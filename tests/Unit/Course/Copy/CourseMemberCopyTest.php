<?php

namespace Tests\Unit\Course\Copy;

use Biz\BaseTestCase;
use Biz\Course\Copy\CourseMemberCopy;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class CourseMemberCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $copy = new CourseMemberCopy($this->biz, [
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ], false);

        self::assertNull($copy->preCopy($this->mockCourse(), []));
    }

    public function testDoCopy()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet([
            'title' => 'testCourseSet',
            'type' => 'normal',
        ]);
        $course = $this->getCourseService()->createCourse($this->mockCourse('测试课程', $courseSet));
        $user = $this->getCurrentUser();
        $this->mockBiz('Course:CourseDao', [
            ['functionName' => 'update', 'returnValue' => []],
        ]);

        $copy = new CourseMemberCopy($this->biz, [
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ], false);

        $copy->doCopy([], ['originCourse' => ['id' => $course['id'], 'courseSetId' => $course['courseSetId']], 'newCourse' => ['id' => $course['id'] + 2, 'courseSetId' => $course['id'] + 2]]);

        $member = $this->getMemberDao()->getByCourseIdAndUserId($course['id'] + 2, $user->getId());

        self::assertNotEmpty($member);
    }

    public function testAfterCopy()
    {
        $copy = new CourseMemberCopy($this->biz, [
            'class' => 'Biz\Course\Copy\CourseMemberCopy',
            'priority' => 100,
        ], false);

        self::assertNull($copy->afterCopy($this->mockCourse(), []));
    }

    protected function mockCourse($title = '测试课程', $courseSet = [])
    {
        return [
            'title' => $title,
            'courseSetId' => empty($courseSet) ? 1 : $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'courseType' => 'normal',
        ];
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

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
