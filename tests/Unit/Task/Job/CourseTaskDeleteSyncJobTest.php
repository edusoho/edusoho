<?php

namespace Tests\Unit\Task\Job;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Task\Job\CourseTaskDeleteEventJob;

class CourseTaskDeleteSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskDeleteEventJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $job->args = ['tasks' => [['id' => 110, 'courseId' => 220, 'copyId' => 1]]];

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
                [
                    'functionName' => 'findByIds',
                    'withParams' => [[231, 232]],
                    'returnValue' => [],
                ],
            ]
        );

        $job->execute();
    }
}
