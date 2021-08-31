<?php

namespace Tests\Unit\Task\Job;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Task\Job\CourseTaskCreateSyncJob;
use Tests\Unit\Task\Job\Tools\MockedText;

class CourseTaskCreateSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskCreateSyncJob();
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
                [
                    'functionName' => 'getCourseTaskByCourseIdAndCopyId',
                    'withParams' => [3331, 110],
                    'returnValue' => [],
                ],
                [
                    'functionName' => 'getCourseTaskByCourseIdAndCopyId',
                    'withParams' => [3332, 110],
                    'returnValue' => [
                        'id' => 110,
                        'courseId' => 3332,
                        'activityId' => 44445,
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
                    'functionName' => 'batchCreate',
                    'withParams' => [
                        [
                            [
                                'courseId' => 3331,
                                'fromCourseSetId' => 222,
                                'createdUserId' => 123,
                                'seq' => 22,
                                'categoryId' => null,
                                'activityId' => null,
                                'title' => 'task title',
                                'isFree' => 1,
                                'isOptional' => 1,
                                'startTime' => 111111111,
                                'endTime' => 111111121,
                                'number' => 123,
                                'mode' => 'task',
                                'type' => 'taskType',
                                'mediaSource' => 'a.png',
                                'copyId' => 110,
                                'maxOnlineNum' => 333,
                                'status' => 'ok',
                                'length' => 3,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'get',
                    'withParams' => [44443],
                    'returnValue' => [
                        'id' => 44443,
                        'copyId' => 0,
                        'mediaType' => 'text',
                        'title' => 'activity title',
                        'remark' => 'activity remark',
                        'content' => 'activity content',
                        'length' => 3,
                        'fromUserId' => 332,
                        'startTime' => 123123123,
                        'endTime' => 123123124,
                        'fromCourseId' => 3330,
                        'finishType' => 'time',
                        'finishData' => 1,
                    ],
                ],
                [
                    'functionName' => 'create',
                    'withParams' => [
                        [
                            'title' => 'activity title',
                            'remark' => 'activity remark',
                            'mediaType' => 'text',
                            'content' => 'activity content',
                            'length' => 3,
                            'fromCourseId' => 3331,
                            'fromCourseSetId' => 222,
                            'fromUserId' => 332,
                            'startTime' => 123123123,
                            'endTime' => 123123124,
                            'copyId' => 44443,
                            'finishType' => 'time',
                            'finishData' => 1,
                        ],
                    ],
                ],
            ]
        );

        $job->execute();

        $this->getTaskDao()->shouldHaveReceived('batchCreate')->times(1);
        $this->getActivityDao()->shouldHaveReceived('create')->times(1);
        $this->getActivityDao()->shouldHaveReceived('get')->times(1);
        $this->getCourseDao()->shouldHaveReceived('findCoursesByParentIdAndLocked')->times(1);
        $this->getTaskService()->shouldHaveReceived('getTask')->times(1);
        $this->getTaskService()->shouldHaveReceived('getCourseTaskByCourseIdAndCopyId')->times(2);

        $mockedText = $this->biz['activity_type.text'];
        $this->assertEquals(
            [
                'id' => 44443,
                'copyId' => 0,
                'mediaType' => 'text',
                'title' => 'activity title',
                'remark' => 'activity remark',
                'content' => 'activity content',
                'length' => 3,
                'fromUserId' => 332,
                'startTime' => 123123123,
                'endTime' => 123123124,
                'fromCourseId' => 3330,
                'finishType' => 'time',
                'finishData' => 1,
            ],
            $mockedText->getCopiedActivity()
        );
    }

    public function testExecuteWithError()
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
//                    'throwException' => new \Exception('error'),
                ],
            ]
        );
        $count = $this->getLogDao()->count([]);
        $job->execute();
        $result = $this->getLogDao()->count([]);
        $this->assertEquals($count + 1, $result);
    }

    public function testCreateMaterials()
    {
        $material = $this->getMaterialDao()->create(
            [
                'title' => 'old title',
                'description' => 'old description',
                'link' => 'old link',
                'fileId' => '111',
                'fileUri' => 'old url',
                'fileMime' => 'mp4',
                'fileSize' => 333,
                'source' => 'old source',
                'userId' => 1,
                'type' => 'material',
                'lessonId' => 1111,
                'courseId' => 2222,
            ]
        );

        $job = new CourseTaskCreateSyncJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);

        ReflectionUtils::invokeMethod(
            $job, 'createMaterials', [
                ['id' => 999],
                ['id' => 1111, 'fromCourseId' => 2222],
                ['courseSetId' => 77, 'id' => 88],
            ]
        );

        $oldMaterials = $this->getMaterialDao()->search(
            ['lessonId' => 1111, 'courseId' => 2222],
            [],
            0,
            PHP_INT_MAX
        );

        $newMaterials = $this->getMaterialDao()->search(
            ['lessonId' => 999, 'courseId' => 88],
            [],
            0,
            PHP_INT_MAX
        );

        $this->assertEquals(1, count($oldMaterials));
        $this->assertEquals(1, count($newMaterials));
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->biz->dao('Course:CourseMaterialDao');
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

    /**
     * @return LogDao
     */
    protected function getLogDao()
    {
        return $this->createDao('System:LogDao');
    }
}
