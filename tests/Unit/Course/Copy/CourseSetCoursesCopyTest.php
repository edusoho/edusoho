<?php

namespace Tests\Unit\Course\Copy;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Course\Copy\CourseSetCoursesCopy;

class CourseSetCoursesCopeTest extends BaseTestCase
{
    public function testDoCopy()
    {
        $courseSet = $this->getCourseSetDao()->create(['id' => 2, 'title' => '']);
        $copy = new CourseSetCoursesCopy($this->biz, [], false);
        $course = $this->getCourseDao()->create(['id' => 33, 'courseSetId' => 1, 'title' => '', 'type' => '', 'originPrice' => '1', 'status' => 'published']);
        $course = $this->getCourseDao()->create(['id' => 34, 'courseSetId' => 1, 'title' => '', 'type' => '', 'originPrice' => '1']);
        $course2 = $this->getCourseDao()->create(['id' => 35, 'courseSetId' => 1, 'title' => '', 'type' => '', 'originPrice' => '10', 'status' => 'published']);
        $source = [
            'id' => 1,
            'defaultCourseId' => $course2['id'],
        ];
        $options = [
            'newCourseSet' => [
                'id' => 2,
                'title' => 2,
            ],
        ];

        $copy->doCopy($source, $options);
        $courses = $this->getCourseDao()->findCoursesByCourseSetIdAndStatus(2, null);

        $this->assertNotEmpty($courses);
        $this->assertEquals(2, $courses[0]['courseSetId']);

        $courseSet = $this->getCourseSetDao()->get($courseSet['id']);
        $this->assertEquals(1, $courseSet['minCoursePrice']);
        $this->assertEquals(10, $courseSet['maxCoursePrice']);
    }

    public function testPreCopy()
    {
        $copy = new CourseSetCoursesCopy($this->biz, [], false);
        $this->assertNull($copy->preCopy([], []));
    }

    public function testGetFields()
    {
        $copy = new CourseSetCoursesCopy($this->biz, [], false);
        $result = ReflectionUtils::invokeMethod($copy, 'getFields');

        $this->assertArrayEquals([
            'title',
            'learnMode',
            'expiryMode',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'summary',
            'goals',
            'audiences',
            'maxStudentNum',
            'price',
            'buyable',
            'tryLookable',
            'tryLookLength',
            'watchLimit',
            'services',
            'taskNum',
            'buyExpiryTime',
            'type',
            'approval',
            'income',
            'originPrice',
            'coinPrice',
            'originCoinPrice',
            'showStudentNumType',
            'serializeMode',
            'giveCredit',
            'about',
            'locationId',
            'address',
            'deadlineNotify',
            'daysOfNotifyBeforeDeadline',
            'useInClassroom',
            'singleBuy',
            'freeStartTime',
            'freeEndTime',
            'locked',
            'maxRate',
            'materialNum',
            'cover',
            'enableFinish',
            'compulsoryTaskNum',
            'rewardPoint',
            'taskRewardPoint',
            'courseType',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'expiryMode',
            'isDefault',
            'parentId',
            'locked',
            'status',
            'teacherIds',
            'lessonNum',
            'publishLessonNum',
            'subtitle',
        ], $result);
    }

    public function testResetCopyId()
    {
        $courseSetId = 2;
        $copy = new CourseSetCoursesCopy($this->biz, [], false);

        $course = $this->getCourseDao()->create(['courseSetId' => $courseSetId, 'parentId' => 1]);
        $task = $this->getTaskDao()->create(['courseId' => 1, 'fromCourseSetId' => $courseSetId, 'title' => '', 'type' => '', 'createdUserId' => '1', 'copyId' => 1]);
        $activity = $this->getActivity()->create(['fromCourseSetId' => $courseSetId, 'copyId' => 1, 'title' => '', 'mediaType' => '']);
        $courseChapter = $this->getCourseChapter()->create(['courseId' => 1, 'copyId' => 1, 'title' => '']);
        $testpaper = $this->getTestpaper()->create(['copyId' => 1, 'courseSetId' => $courseSetId]);
        ReflectionUtils::invokeMethod($copy, 'resetCopyId', [$courseSetId]);

        $course = $this->getCourseDao()->get($course['id']);
        $this->assertEquals(0, $course['parentId']);
        $task = $this->getTaskDao()->get($task['id']);
        $this->assertEquals(0, $task['copyId']);
        $activity = $this->getActivity()->get($activity['id']);
        $this->assertEquals(0, $activity['copyId']);
        $testpaper = $this->getTestpaper()->get($testpaper['id']);
        $this->assertEquals(0, $testpaper['copyId']);
        $courseChapter = $this->getCourseChapter()->get($courseChapter['id']);
        $this->assertEquals(0, $courseChapter['copyId']);
    }

    protected function getQuestion()
    {
        return $this->biz->dao('Question:QuestionDao');
    }

    protected function getTestpaper()
    {
        return $this->biz->dao('Testpaper:TestpaperDao');
    }

    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    protected function getActivity()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    protected function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    protected function getCourseChapter()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }
}
