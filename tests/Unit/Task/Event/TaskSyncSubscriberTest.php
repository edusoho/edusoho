<?php

namespace Tests\Unit\Task\Event;

use Biz\BaseTestCase;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Service\CourseService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Event\TaskSyncSubscriber;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Dao\JobDao;

class TaskSyncSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $expected = array(
            'course.task.create' => 'onCourseTaskCreate',
            'course.task.update' => 'onCourseTaskUpdate',
            'course.task.updateOptional' => 'onCourseTaskUpdate',
            'course.task.delete' => 'onCourseTaskDelete',
            'course.task.publish' => 'onCourseTaskPublish',
            'course.task.unpublish' => 'onCourseTaskUnpublish',
        );
        $this->assertEquals($expected, TaskSyncSubscriber::getSubscribedEvents());
    }

    public function testOnCourseTaskCreateWithCopyId()
    {
        $event = new Event(array(
            'copyId' => 1,
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskCreate($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskCreateWithCopiedCourseEmpty()
    {
        $event = new Event(array(
            'copyId' => 0,
            'courseId' => 1,
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskCreate($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskCreate()
    {
        $this->mockBiz('Course:CourseDao', array(
            array(
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => array(array('id' => 1)),
                'withParams' => array(1, 1),
            ),
        ));

        $event = new Event(array(
            'id' => 1,
            'copyId' => 0,
            'courseId' => 1,
        ));

        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $eventSubscriber->onCourseTaskCreate($event);

        $job = $this->getSchedulerJobDao()->getByName('course_task_create_sync_job_1');
        $this->assertNotEmpty($job);
        $this->assertEquals('Biz\Task\Job\CourseTaskCreateSyncJob', $job['class']);
    }

    public function testOnCourseTaskUpdateWithCopyId()
    {
        $event = new Event(array(
            'copyId' => 1,
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskUpdate($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskUpdateWithCopiedCourseEmpty()
    {
        $event = new Event(array(
            'copyId' => 0,
            'courseId' => 1,
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskUpdate($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskUpdate()
    {
        $this->mockBiz('Course:CourseDao', array(
            array(
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => array(array('id' => 1)),
                'withParams' => array(1, 1),
            ),
        ));

        $event = new Event(array(
            'id' => 1,
            'copyId' => 0,
            'courseId' => 1,
        ));

        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $eventSubscriber->onCourseTaskUpdate($event);

        $job = $this->getSchedulerJobDao()->getByName('course_task_update_sync_job_1');
        $this->assertNotEmpty($job);
        $this->assertEquals('Biz\Task\Job\CourseTaskUpdateSyncJob', $job['class']);
    }

    public function testOnCourseTaskPublishWithCopyId()
    {
        $event = new Event(array(
            'copyId' => 1,
            'status' => 'testStatus',
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskPublish($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskPublishWithCopiedCourseEmpty()
    {
        $event = new Event(array(
            'copyId' => 0,
            'courseId' => 1,
            'status' => 'testStatus',
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskPublish($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskPublishWithDefaultCourse()
    {
        $parentCourse = $this->createCourse(1);
        $parentTask = $this->createTask(1, $parentCourse['id'], 0, 'published');
        $childCourse = $this->createCourse(2, $parentCourse['id']);
        $chileTask = $this->createTask(2, $childCourse['id'], $parentTask['id']);

        $event = new Event(array(
            'id' => 1,
            'copyId' => 0,
            'courseType' => $parentCourse['courseType'],
            'courseId' => $parentCourse['id'],
            'status' => 'testStatus',
            'categoryId' => 1,
        ));

        $eventSubscriber = new TaskSyncSubscriber($this->biz);

        $eventSubscriber->onCourseTaskPublish($event);

        $parentTaskResult = $this->getTaskDao()->get($parentTask['id']);
        $childTaskResult = $this->getTaskDao()->get($chileTask['id']);

        $this->assertEquals('published', $parentTask['status']);
        $this->assertEquals('published', $parentTaskResult['status']);
        $this->assertEquals('create', $chileTask['status']);
        $this->assertEquals('published', $childTaskResult['status']);
    }

    public function testOnCourseTaskPublishWithOtherCourseType()
    {
        $parentCourse = $this->createCourse(1, 0, 'normal');
        $parentTask = $this->createTask(1, $parentCourse['id'], 0, 'published');
        $childCourse = $this->createCourse(2, $parentCourse['id'], 'normal');
        $chileTask = $this->createTask(2, $childCourse['id'], $parentTask['id']);

        $event = new Event(array(
            'id' => 1,
            'copyId' => 0,
            'courseType' => $parentCourse['courseType'],
            'courseId' => $parentCourse['id'],
            'status' => 'testStatus',
            'categoryId' => 1,
        ));

        $eventSubscriber = new TaskSyncSubscriber($this->biz);

        $eventSubscriber->onCourseTaskPublish($event);

        $parentTaskResult = $this->getTaskDao()->get($parentTask['id']);
        $childTaskResult = $this->getTaskDao()->get($chileTask['id']);

        $this->assertEquals('published', $parentTask['status']);
        $this->assertEquals('published', $parentTaskResult['status']);
        $this->assertEquals('create', $chileTask['status']);
        $this->assertEquals('published', $childTaskResult['status']);
    }

    public function testOnCourseTaskUnPublishWithCopyId()
    {
        $event = new Event(array(
            'copyId' => 1,
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskUnpublish($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskUnPublishWithCopiedCourseEmpty()
    {
        $event = new Event(array(
            'copyId' => 0,
            'courseId' => 1,
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskUnpublish($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskUnPublishWithDefaultCourse()
    {
        $parentCourse = $this->createCourse(1);
        $parentTask = $this->createTask(1, $parentCourse['id'], 0, 'unpublished');
        $childCourse = $this->createCourse(2, $parentCourse['id']);
        $chileTask = $this->createTask(2, $childCourse['id'], $parentTask['id'], 'published');

        $event = new Event(array(
            'id' => 1,
            'copyId' => 0,
            'courseType' => $parentCourse['courseType'],
            'courseId' => $parentCourse['id'],
            'categoryId' => 1,
        ));

        $eventSubscriber = new TaskSyncSubscriber($this->biz);

        $eventSubscriber->onCourseTaskUnpublish($event);

        $parentTaskResult = $this->getTaskDao()->get($parentTask['id']);
        $childTaskResult = $this->getTaskDao()->get($chileTask['id']);

        $this->assertEquals('unpublished', $parentTask['status']);
        $this->assertEquals('unpublished', $parentTaskResult['status']);
        $this->assertEquals('published', $chileTask['status']);
        $this->assertEquals('unpublished', $childTaskResult['status']);
    }

    public function testOnCourseTaskUnPublishWithOtherCourseType()
    {
        $parentCourse = $this->createCourse(1, 0, 'normal');
        $parentTask = $this->createTask(1, $parentCourse['id'], 0, 'unpublished');
        $childCourse = $this->createCourse(2, $parentCourse['id'], 'normal');
        $chileTask = $this->createTask(2, $childCourse['id'], $parentTask['id'], 'published');

        $event = new Event(array(
            'id' => 1,
            'copyId' => 0,
            'courseType' => $parentCourse['courseType'],
            'courseId' => $parentCourse['id'],
            'categoryId' => 1,
        ));

        $eventSubscriber = new TaskSyncSubscriber($this->biz);

        $eventSubscriber->onCourseTaskUnpublish($event);

        $parentTaskResult = $this->getTaskDao()->get($parentTask['id']);
        $childTaskResult = $this->getTaskDao()->get($chileTask['id']);

        $this->assertEquals('unpublished', $parentTask['status']);
        $this->assertEquals('unpublished', $parentTaskResult['status']);
        $this->assertEquals('published', $chileTask['status']);
        $this->assertEquals('unpublished', $childTaskResult['status']);
    }

    public function testOnCourseTaskDeleteWithCopyId()
    {
        $event = new Event(array('copyId' => 1));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);

        $result = $eventSubscriber->onCourseTaskDelete($event);
        $this->assertNull($result);
    }

    public function testOnCourseTaskDeleteWithCopiedCourseEmpty()
    {
        $event = new Event(array(
            'copyId' => 0,
            'courseId' => 1,
        ));
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskDelete($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskDelete()
    {
        $this->mockBiz('Course:CourseDao', array(
            array(
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => array(array('id' => 1)),
                'withParams' => array(1, 1),
            ),
        ));

        $event = new Event(array(
            'id' => 1,
            'copyId' => 0,
            'courseId' => 1,
        ));

        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $eventSubscriber->onCourseTaskDelete($event);

        $job = $this->getSchedulerJobDao()->getByName('course_task_delete_sync_job_1');
        $this->assertNotEmpty($job);
        $this->assertEquals('Biz\Task\Job\CourseTaskDeleteSyncJob', $job['class']);
        $this->assertEquals(array('taskId' => 1, 'courseId' => 1), $job['args']);
    }

    private function createCourse($courseId, $parentId = 0, $courseType = CourseService::DEFAULT_COURSE_TYPE)
    {
        $course = array(
            'id' => $courseId,
            'courseSetId' => $courseId,
            'title' => 'testCourseTitle',
            'courseSetTitle' => 'testCourseSetTitle',
            'seq' => 0,
            'parentId' => $parentId,
            'ratingNum' => 0,
            'rating' => 0,
            'noteNum' => 0,
            'type' => 'normal',
            'approval' => 0,
            'income' => 0.00,
            'originPrice' => 0.00,
            'coinPrice' => 0.00,
            'originCoinPrice' => 0.00,
            'showStudentNumType' => 'opened',
            'serializeMode' => 'none',
            'giveCredit' => 0,
            'locationId' => 0,
            'address' => 'testAddress',
            'deadlineNotify' => 'none',
            'daysOfNotifyBeforeDeadline' => 0,
            'useInClassroom' => 'single',
            'singleBuy' => 1,
            'freeStartTime' => 0,
            'freeEndTime' => 0,
            'locked' => $parentId ? 1 : 0,
            'buyExpiryTime' => 0,
            'enableFinish' => 1,
            'materialNum' => 0,
            'compulsoryTaskNum' => 0,
            'lessonNum' => 0,
            'publishLessonNum' => 0,
            'createdTime' => null,
            'updatedTime' => 0,
            'creator' => 1,
            'recommended' => 0,
            'recommendedSeq' => 0,
            'recommendedTime' => 0,
            'categoryId' => 0,
            'showServices' => 0,
            'hitNum' => 0,
            'courseType' => $courseType,
            'enableAudio' => 0,
            'rewardPoint' => 0,
            'taskRewardPoint' => 0,
            'isHideUnpublish' => 0,
        );

        return $this->getCourseDao()->create($course);
    }

    private function createTask($taskId, $courseId, $copyId = 0, $status = 'create')
    {
        $task = array(
            'id' => $taskId,
            'courseId' => $courseId,
            'seq' => 2,
            'categoryId' => 1,
            'activityId' => $courseId,
            'title' => 'test task',
            'isFree' => 0,
            'isOptional' => 0,
            'startTime' => 0,
            'endTime' => 0,
            'mode' => 'lesson',
            'status' => $status,
            'number' => 1,
            'type' => 'text',
            'mediaSource' => '',
            'maxOnlineNum' => 0,
            'fromCourseSetId' => $courseId,
            'length' => 0,
            'copyId' => $copyId,
            'createdUserId' => 2,
            'createdTime' => time(),
            'updatedTime' => time(),
        );

        return $this->getTaskDao()->create($task);
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    /**
     * @return JobDao
     */
    private function getSchedulerJobDao()
    {
        return $this->createDao('Scheduler:JobDao');
    }
}
