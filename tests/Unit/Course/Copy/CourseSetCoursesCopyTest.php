<?php

namespace Tests\Unit\Course\Copy;

use Biz\BaseTestCase;
use Biz\Course\Copy\CourseSetCoursesCopy;
use AppBundle\Common\ReflectionUtils;

class CourseSetCoursesCopeTest extends BaseTestCase
{
    public function testDoCopy()
    {
        $courseSet = $this->getCourseSetDao()->create(array('id' => 2, 'title' => ''));
        $copy = new CourseSetCoursesCopy($this->biz, array(), false);
        $course = $this->getCourseDao()->create(array('id' => 33, 'courseSetId' => 1, 'title' => '', 'type' => '', 'originPrice' => '1', 'status' => 'published'));
        $course = $this->getCourseDao()->create(array('id' => 34, 'courseSetId' => 1, 'title' => '', 'type' => '', 'originPrice' => '1'));
        $course2 = $this->getCourseDao()->create(array('id' => 35, 'courseSetId' => 1, 'title' => '', 'type' => '', 'originPrice' => '10', 'status' => 'published'));
        $source = array(
            'id' => 1,
            'defaultCourseId' => $course2['id'],
        );
        $options = array(
            'newCourseSet' => array(
                'id' => 2,
                'title' => 2,
            ),
        );

        $copy->doCopy($source, $options);
        $courses = $this->getCourseDao()->findCoursesByCourseSetIdAndStatus(2, null);

        $this->assertNotEmpty($courses);
        $this->assertEquals(2, $courses[0]['courseSetId']);

        $courseSet = $this->getCourseSetDao()->get($courseSet['id']);
        $this->assertEquals(1, $courseSet['minCoursePrice']);
        $this->assertEquals(10, $courseSet['maxCoursePrice']);
    }

    public function testUpdateQuestionsCourseId()
    {
        $courseSetId = 11;
        $copy = new CourseSetCoursesCopy($this->biz, array(), false);
        $course = $this->getCourseDao()->create(array('courseSetId' => $courseSetId, 'title' => '', 'type' => ''));
        $course2 = $this->getCourseDao()->create(array('courseSetId' => $courseSetId, 'title' => '', 'type' => '', 'parentId' => $course['id']));
        $question = $this->getQuestion()->create(array('copyId' => 1, 'courseSetId' => $courseSetId));
        $question2 = $this->getQuestion()->create(array('copyId' => $question['id'], 'courseSetId' => $courseSetId, 'courseId' => $course['id']));
        ReflectionUtils::invokeMethod($copy, 'updateQuestionsCourseId', array($courseSetId));
        $question = $this->getQuestion()->get($question['id']);
        $question2 = $this->getQuestion()->get($question2['id']);

        $this->assertEquals(0, $question['courseId']);
        $this->assertEquals($course2['id'], $question2['courseId']);
    }

    public function testUpdateQuestionsLessonId()
    {
        $courseSetId = 11;
        $copy = new CourseSetCoursesCopy($this->biz, array(), false);
        $task = $this->getTaskDao()->create(array('courseId' => 1, 'fromCourseSetId' => $courseSetId, 'title' => '', 'type' => '', 'createdUserId' => '1'));
        $task2 = $this->getTaskDao()->create(array('courseId' => 1, 'fromCourseSetId' => $courseSetId, 'title' => '', 'type' => '', 'createdUserId' => '1', 'copyId' => $task['id']));
        $question = $this->getQuestion()->create(array('copyId' => 1, 'courseSetId' => $courseSetId));
        $question2 = $this->getQuestion()->create(array('copyId' => $question['id'], 'courseSetId' => $courseSetId, 'lessonId' => $task['id']));
        ReflectionUtils::invokeMethod($copy, 'updateQuestionsLessonId', array($courseSetId));
        $question = $this->getQuestion()->get($question['id']);
        $question2 = $this->getQuestion()->get($question2['id']);
        $this->assertEquals(0, $question['lessonId']);
        $this->assertEquals($task2['id'], $question2['lessonId']);
    }

    public function testUpdateExerciseRange()
    {
        $courseSetId = 11;
        $copy = new CourseSetCoursesCopy($this->biz, array(), false);
        $task = $this->getTaskDao()->create(array('courseId' => 1, 'fromCourseSetId' => $courseSetId, 'title' => '', 'type' => '', 'createdUserId' => '1', 'copyId' => 10));
        $testpaper = $this->getTestpaper()->create(array('lessonId' => 10, 'copyId' => 1, 'type' => 'exercise', 'courseSetId' => $courseSetId, 'metas' => array('range' => array('lessonId' => 10))));

        ReflectionUtils::invokeMethod($copy, 'updateExerciseRange', array($courseSetId));
        $testpaper = $this->getTestpaper()->get($testpaper['id']);
        $this->assertEquals($task['id'], $testpaper['metas']['range']['lessonId']);
    }

    public function testPreCopy()
    {
        $copy = new CourseSetCoursesCopy($this->biz, array(), false);
        $this->assertNull($copy->preCopy(array(), array()));
    }

    public function testGetFields()
    {
        $copy = new CourseSetCoursesCopy($this->biz, array(), false);
        $result = ReflectionUtils::invokeMethod($copy, 'getFields');

        $this->assertArrayEquals(array(
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
        ), $result);
    }

    public function testResetCopyId()
    {
        $courseSetId = 2;
        $copy = new CourseSetCoursesCopy($this->biz, array(), false);

        $course = $this->getCourseDao()->create(array('courseSetId' => $courseSetId, 'parentId' => 1));
        $task = $this->getTaskDao()->create(array('courseId' => 1, 'fromCourseSetId' => $courseSetId, 'title' => '', 'type' => '', 'createdUserId' => '1', 'copyId' => 1));
        $activity = $this->getActivity()->create(array('fromCourseSetId' => $courseSetId, 'copyId' => 1, 'title' => '', 'mediaType' => ''));
        $courseChapter = $this->getCourseChapter()->create(array('courseId' => 1, 'copyId' => 1, 'title' => ''));
        $testpaper = $this->getTestpaper()->create(array('copyId' => 1, 'courseSetId' => $courseSetId));
        $question = $this->getQuestion()->create(array('copyId' => 1, 'courseSetId' => $courseSetId));
        ReflectionUtils::invokeMethod($copy, 'resetCopyId', array($courseSetId));

        $course = $this->getCourseDao()->get($course['id']);
        $this->assertEquals(0, $course['parentId']);
        $task = $this->getTaskDao()->get($task['id']);
        $this->assertEquals(0, $task['copyId']);
        $activity = $this->getActivity()->get($activity['id']);
        $this->assertEquals(0, $activity['copyId']);
        $testpaper = $this->getTestpaper()->get($testpaper['id']);
        $this->assertEquals(0, $testpaper['copyId']);
        $question = $this->getQuestion()->get($question['id']);
        $this->assertEquals(0, $question['copyId']);
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
