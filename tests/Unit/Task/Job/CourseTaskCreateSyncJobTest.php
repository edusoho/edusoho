<?php

namespace Tests\Unit\Task\Job;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Task\Job\CourseTaskCreateSyncJob;

class CourseTaskCreateSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskCreateSyncJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $job->args = ['taskId' => 110];

        $this->mockBiz(
            'Task:TaskService',
            [
                [
                    'functionName' => 'getTask',
                    'withParams' => [110],
                    'returnValue' => [
                        'id' => 110,
                        'courseId' => 3330,
                        'activityId' => 44443,
                        'createdUserId' => 123,
                        'seq' => 22,
                        'categoryId' => 123,
                        'title' => 'task title',
                        'isLesson' => 1,
                        'isFree' => 1,
                        'isOptional' => 1,
                        'startTime' => 111111111,
                        'endTime' => 111111121,
                        'number' => 123,
                        'mode' => 'task',
                        'type' => 'taskType',
                        'mediaSource' => 'a.png',
                        'maxOnlineNum' => 333,
                        'status' => 'ok',
                        'length' => 3,
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Course:CourseDao',
            [
                [
                    'functionName' => 'findCoursesByParentIdAndLocked',
                    'withParams' => [3330, 1],
                    'returnValue' => [
                        ['id' => 3331, 'courseSetId' => 222],
                        ['id' => 3332],
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'Task:TaskDao',
            [
                [
                    'functionName' => 'findByCopyIdAndLockedCourseIds',
                    'returnValue' => [],
                ],
            ]
        );

        $job->execute();

        $this->getCourseDao()->shouldHaveReceived('findCoursesByParentIdAndLocked')->times(2);
        $this->getTaskService()->shouldHaveReceived('getTask')->times(1);
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->dao('Task:TaskService');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
