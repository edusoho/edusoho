<?php

namespace Tests\Unit\Task\Job;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Task\Job\CourseTaskDeleteEventJob;
use Tests\Unit\Task\Job\Tools\MockedNormalStrategy;

class CourseTaskDeleteSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskDeleteEventJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $job->args = ['taskId' => 110, 'courseId' => 220];

        $this->biz['course.normal_strategy'] = new MockedNormalStrategy();

        $this->mockBiz(
            'Course:CourseDao',
            [
                [
                    'functionName' => 'findCoursesByParentIdAndLocked',
                    'returnValue' => [
                        ['id' => 3331, 'courseSetId' => 222, 'courseType' => 'normal'],
                        ['id' => 3332, 'courseType' => 'normal'],
                    ],
                ],
                [
                    'functionName' => 'get',
                    'returnValue' => [
                        'id' => 220,
                    ],
                ],
                [
                    'functionName' => 'update',
                    'returnValue' => [
                        'id' => 220,
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'recountLearningDataByCourseId',
                    'returnValue' => [
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'Task:TaskDao',
            [
                [
                    'functionName' => 'findByCopyIdAndLockedCourseIds',
                    'returnValue' => [
                        ['id' => 231, 'courseId' => 3331],
                        ['id' => 232, 'courseId' => 3332],
                    ],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [231],
                    'returnValue' => [
                        'id' => 231,
                        'courseId' => 3331,
                    ],
                ],
                [
                    'functionName' => 'count',
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [232],
                    'returnValue' => [
                        'id' => 232,
                        'courseId' => 3332,
                    ],
                ],
            ]
        );

        $job->execute();

        $this->getTaskDao()->shouldHaveReceived('findByCopyIdAndLockedCourseIds')->times(3);
        $this->getTaskDao()->shouldHaveReceived('get')->times(2);

        $strategy = $this->biz['course.normal_strategy'];

        $this->assertEquals(
            [
                ['id' => 231, 'courseId' => 3331],
                ['id' => 232, 'courseId' => 3332],
            ],
            $strategy->getDeletedTasks()
        );
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
}
