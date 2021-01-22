<?php

namespace Tests\Unit\Task\Job;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Task\Job\CourseTaskUpdateSyncJob;
use Tests\Unit\Task\Job\Tools\MockedText;

class CourseTaskUpdateSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskUpdateSyncJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $job->args = ['taskId' => 110];

        $this->biz['activity_type.text'] = new MockedText($this->biz);
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
            'Course:CourseDao',
            [
                [
                    'functionName' => 'findCoursesByParentIdAndLocked',
                    'withParams' => [3330, 1],
                    'returnValue' => [
                        ['id' => 3331],
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
                    'withParams' => [110, [3331, 3332]],
                    'returnValue' => [
                        ['id' => 20, 'activityId' => 44441, 'fromCourseSetId' => 55551, 'courseId' => 3331, 'mediaType' => 'text'],
                        ['id' => 21, 'activityId' => 44442, 'fromCourseSetId' => 55552, 'courseId' => 3332, 'mediaType' => 'text'],
                    ],
                ],
                [
                    'functionName' => 'batchUpdate',
                    'withParams' => [
                        [20, 21],
                        [
                            20 => ['id' => 20, 'activityId' => 44441, 'fromCourseSetId' => 55551, 'courseId' => 3331, 'mediaType' => 'text'],
                            21 => ['id' => 21, 'activityId' => 44442, 'fromCourseSetId' => 55552, 'courseId' => 3332, 'mediaType' => 'text'],
                        ],
                        'id',
                    ],
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
            ]
        );
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'get',
                    'withParams' => [44441],
                    'returnValue' => [
                        'id' => 44441,
                        'copyId' => 44451,
                        'mediaType' => 'text',
                    ],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [44442],
                    'returnValue' => [
                        'id' => 44442,
                        'copyId' => 44452,
                        'mediaType' => 'text',
                    ],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [44451],
                    'returnValue' => [
                        'id' => 44451,
                        'copyId' => 0,
                        'mediaType' => 'text',
                    ],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [44452],
                    'returnValue' => [
                        'id' => 44452,
                        'copyId' => 0,
                        'mediaType' => 'text',
                    ],
                ],
                [
                    'functionName' => 'update',
                    'withParams' => [
                        44441,
                        ['id' => 44441, 'copyId' => 44451, 'mediaType' => 'text', 'mediaId' => 2222222],
                    ],
                ],
                [
                    'functionName' => 'update',
                    'withParams' => [
                        44442,
                        ['id' => 44442, 'copyId' => 44452, 'mediaType' => 'text', 'mediaId' => 2222222],
                    ],
                ],
            ]
        );

        $job->execute();

        $this->getTaskService()->shouldHaveReceived('getTask')->times(1);
        $this->getCourseDao()->shouldHaveReceived('findCoursesByParentIdAndLocked')->times(1);
        $this->getTaskDao()->shouldHaveReceived('findByCopyIdAndLockedCourseIds')->times(2);
        $this->getTaskDao()->shouldHaveReceived('batchUpdate')->times(1);
        $this->getActivityDao()->shouldHaveReceived('get')->times(4);
        $this->getActivityDao()->shouldHaveReceived('update')->times(2);

        $mockedText = $this->biz['activity_type.text'];

        $this->assertArrayEquals(
            ['id' => 44452, 'copyId' => 0, 'mediaType' => 'text'],
            $mockedText->getSourceActivity()
        );

        $this->assertArrayEquals(
            ['id' => 44442, 'copyId' => 44452, 'mediaType' => 'text'],
            $mockedText->getSyncActivity()
        );
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

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }
}
