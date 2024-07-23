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
        $expected = [
            'course.task.create' => 'onCourseTaskCreate',
            'course.task.update' => 'onCourseTaskUpdate',
            'course.task.updateOptional' => 'onCourseTaskUpdate',
            'course.task.delete' => 'onCourseTaskDelete',
            'course.task.publish' => 'onCourseTaskPublish',
            'course.task.unpublish' => 'onCourseTaskUnpublish',
        ];
        $this->assertEquals($expected, TaskSyncSubscriber::getSubscribedEvents());
    }

    public function testOnCourseTaskCreateWithCopyId()
    {
        $event = new Event([
            'copyId' => 1,
        ]);
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskCreate($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskCreateWithCopiedCourseEmpty()
    {
        $event = new Event([
            'copyId' => 0,
            'courseId' => 1,
        ]);
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskCreate($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskCreate()
    {
        $this->mockBiz('Course:CourseDao', [
            [
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => [['id' => 1, 'courseSetId' => 1]],
                'withParams' => [1, 1],
            ],
        ]);
        $this->mockBiz('Activity:ActivityDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1],
                'withParams' => [1],
            ],
            [
                'functionName' => 'findByCopyIdAndCourseIds',
                'returnValue' => [['id' => 2, 'fromCourseId' => 1, 'fromCourseSetId' => 1]],
                'withParams' => [1, [1]],
            ],
            [
                'functionName' => 'batchCreate',
            ],
        ]);
        $this->mockBiz('Course:CourseMaterialDao', [
            [
                'functionName' => 'search',
                'returnValue' => [],
            ],
        ]);
        $this->mockBiz('Task:TaskDao', [
            [
                'functionName' => 'findByCopyIdAndLockedCourseIds',
                'returnValue' => [],
            ],
            [
                'functionName' => 'batchCreate',
                'returnValue' => [],
            ],
        ]);

        $event = new Event([
            'id' => 1,
            'activityId' => 1,
            'copyId' => 0,
            'courseId' => 1,
        ]);

        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $eventSubscriber->onCourseTaskCreate($event);

        $job = $this->getSchedulerJobDao()->getByName('course_task_create_sync_job_1');
        $this->assertNotEmpty($job);
        $this->assertEquals('Biz\Task\Job\CourseTaskCreateSyncJob', $job['class']);
    }

    public function testOnCourseTaskUpdateWithCopyId()
    {
        $event = new Event([
            'copyId' => 1,
        ]);
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskUpdate($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskUpdateWithCopiedCourseEmpty()
    {
        $event = new Event([
            'copyId' => 0,
            'courseId' => 1,
        ]);
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskUpdate($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskUpdate()
    {
        $this->mockBiz('Course:CourseDao', [
            [
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => [['id' => 1]],
                'withParams' => [1, 1],
            ],
        ]);
        $this->mockBiz('Task:TaskDao', [
            [
                'functionName' => 'findByCopyIdAndLockedCourseIds',
                'withParams' => [1, [1]],
                'returnValue' => [['id' => 2, 'activityId' => 2]],
            ],
            [
                'functionName' => 'update',
                'withParams' => [['ids' => [2]], []],
            ],
        ]);
        $this->mockBiz('Activity:ActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => ['mediaType' => 'text'],
            ],
            [
                'functionName' => 'findByIds',
                'withParams' => [[2]],
                'returnValue' => ['mediaType' => 'text'],
            ],
            [
                'functionName' => 'update',
                'withParams' => [['ids' => [2]], []],
            ],
        ]);

        $event = new Event([
            'id' => 1,
            'copyId' => 0,
            'courseId' => 1,
            'activityId' => 1,
        ]);

        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $eventSubscriber->onCourseTaskUpdate($event);

        $job = $this->getSchedulerJobDao()->getByName('course_task_update_sync_job_1');
        $this->assertNotEmpty($job);
        $this->assertEquals('Biz\Task\Job\CourseTaskUpdateSyncJob', $job['class']);
    }

    public function testOnCourseTaskPublishWithCopyId()
    {
        $event = new Event([
            'copyId' => 1,
            'status' => 'testStatus',
        ]);
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskPublish($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskPublishWithCopiedCourseEmpty()
    {
        $event = new Event([
            'copyId' => 0,
            'courseId' => 1,
            'status' => 'testStatus',
        ]);
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

        $event = new Event([
            'id' => 1,
            'copyId' => 0,
            'courseType' => $parentCourse['courseType'],
            'courseId' => $parentCourse['id'],
            'status' => 'testStatus',
            'categoryId' => 1,
        ]);

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

        $event = new Event([
            'id' => 1,
            'copyId' => 0,
            'courseType' => $parentCourse['courseType'],
            'courseId' => $parentCourse['id'],
            'status' => 'testStatus',
            'categoryId' => 1,
        ]);

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
        $event = new Event([
            'copyId' => 1,
        ]);
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskUnpublish($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskUnPublishWithCopiedCourseEmpty()
    {
        $event = new Event([
            'copyId' => 0,
            'courseId' => 1,
        ]);
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

        $event = new Event([
            'id' => 1,
            'copyId' => 0,
            'courseType' => $parentCourse['courseType'],
            'courseId' => $parentCourse['id'],
            'categoryId' => 1,
        ]);

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

        $event = new Event([
            'id' => 1,
            'copyId' => 0,
            'courseType' => $parentCourse['courseType'],
            'courseId' => $parentCourse['id'],
            'categoryId' => 1,
        ]);

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
        $event = new Event(['copyId' => 1]);
        $eventSubscriber = new TaskSyncSubscriber($this->biz);

        $result = $eventSubscriber->onCourseTaskDelete($event);
        $this->assertNull($result);
    }

    public function testOnCourseTaskDeleteWithCopiedCourseEmpty()
    {
        $event = new Event([
            'copyId' => 0,
            'courseId' => 1,
        ]);
        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $result = $eventSubscriber->onCourseTaskDelete($event);

        $this->assertNull($result);
    }

    public function testOnCourseTaskDelete()
    {
        $this->mockBiz('Course:CourseDao', [
            [
                'functionName' => 'findCoursesByParentIdAndLocked',
                'withParams' => [1, 1],
                'returnValue' => [['id' => 2, 'courseSetId' => 2]],
            ],
        ]);
        $this->mockBiz('Task:TaskDao', [
            [
                'functionName' => 'findByCopyIdAndLockedCourseIds',
                'withParams' => [1, [2]],
                'returnValue' => [['id' => 2], ['id' => 3], ['id' => 4], ['id' => 5], ['id' => 6], ['id' => 7]],
            ],
            [
                'functionName' => 'batchDelete',
                'withParams' => [['ids' => [2, 3, 4, 5, 6, 7]]],
            ],
            [
                'functionName' => 'findByIds',
                'withParams' => [[2, 3, 4, 5, 6, 7]],
                'returnValue' => [['id' => 2, 'activityId' => 2], ['id' => 3, 'activityId' => 3], ['id' => 4, 'activityId' => 4], ['id' => 5, 'activityId' => 5], ['id' => 6, 'activityId' => 6], ['id' => 7, 'activityId' => 7]],
            ],
            [
                'functionName' => 'search',
                'returnValue' => [],
            ],
        ]);

        $event = new Event([
            'id' => 1,
            'copyId' => 0,
            'courseId' => 1,
        ]);

        $eventSubscriber = new TaskSyncSubscriber($this->biz);
        $eventSubscriber->onCourseTaskDelete($event);

        $job = $this->getSchedulerJobDao()->getByName('activity_delete_job_2');
        $this->assertNotEmpty($job);
        $this->assertEquals('Biz\Activity\Job\DeleteActivityJob', $job['class']);
        $this->assertEquals(['ids' => [2, 3, 4, 5, 6, 7]], $job['args']);

        $job = $this->getSchedulerJobDao()->getByName('task_delete_event_job_2');
        $this->assertEquals('Biz\Task\Job\CourseTaskDeleteEventJob', $job['class']);
    }

    private function createCourse($courseId, $parentId = 0, $courseType = CourseService::DEFAULT_COURSE_TYPE)
    {
        $course = [
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
        ];

        return $this->getCourseDao()->create($course);
    }

    private function createTask($taskId, $courseId, $copyId = 0, $status = 'create')
    {
        $task = [
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
        ];

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
