<?php

namespace Tests\Unit\Task\Job;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Task\Job\CourseTaskUpdateSyncJob;

class CourseTaskUpdateSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskUpdateSyncJob();
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
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Task:TaskDao',
            [
                [
                    'functionName' => 'findByCopyIdAndLockedCourseIds',
                    'withParams' => [110, [3331, 3332]],
                    'returnValue' => [
                        ['id' => 20, 'activityId' => 44441, 'fromCourseSetId' => 55551, 'courseId' => 3331, 'mediaType' => 'text'],
                        ['id' => 21, 'activityId' => 44442, 'fromCourseSetId' => 55552, 'courseId' => 3332, 'mediaType' => 'text'],
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'findCoursesByParentIdAndLocked',
                    'withParams' => [3330, 1],
                    'returnValue' => [
                        ['id' => 3331],
                        ['id' => 3332],
                    ],
                ],
                [
                    'functionName' => 'getCourse',
                    'returnValue' => [],
                ],
                [
                    'functionName' => 'updateCourseStatistics',
                    'returnValue' => [],
                ],
            ]
        );

        $job->execute();

        $this->getTaskService()->shouldHaveReceived('getTask')->times(1);
        $this->getTaskDao()->shouldHaveReceived('findByCopyIdAndLockedCourseIds')->times(2);
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->dao('Task:TaskService');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }
}
