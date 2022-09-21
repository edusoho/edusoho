<?php

namespace Tests\Unit\Activity\Service;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\TimeMachine;
use Biz\Activity\Service\ActivityService;
use Biz\BaseTestCase;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;

class ActivityServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'tryManageCourse',
                    'returnValue' => ['id' => 1],
                ],
            ]
        );
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testCreateActivityWhenInvalidArgument()
    {
        $activity = [
            'title' => 'test activity',
        ];

        $savedActivity = $this->getActivityService()->createActivity($activity);
        $this->assertEquals($activity['title'], $savedActivity['title']);
    }

    // /**
    //  * @expectedException \AccessDeniedException
    //  */
    //
    // public function testCreateActivityWhenAccessDenied()
    // {
    //     $activity = array(
    //         'title' => 'test activity'
    //     );
    //     $savedActivity = $this->getActivityService()->createActivity($activity);
    //     $this->assertEquals($activity['title'], $savedActivity['title']);
    // }

    public function testCreateActivity()
    {
        $activity = [
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        ];
        $savedActivity = $this->getActivityService()->createActivity($activity);
        $this->assertEquals($activity['title'], $savedActivity['title']);
    }

    public function testUpdateActivity()
    {
        $activity = [
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        ];
        $savedActivity = $this->getActivityService()->createActivity($activity);

        $activity['title'] = 'course activity';
        $savedActivity = $this->getActivityService()->updateActivity($savedActivity['id'], $activity);

        $this->assertEquals($activity['title'], $savedActivity['title']);
    }

    public function testDeleteActivity()
    {
        $activity = [
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        ];
        $savedActivity = $this->getActivityService()->createActivity($activity);

        $this->assertNotNull($savedActivity);

        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'tryManageCourse',
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'updateCourseStatistics',
                    'returnValue' => 1,
                ],
            ]
        );

        $this->mockBiz(
            'Course:MaterialService',
            [
                [
                    'functionName' => 'deleteMaterialsByLessonId',
                    'returnValue' => 1,
                ],
            ]
        );
        $this->getActivityService()->deleteActivity($savedActivity['id']);

        $savedActivity = $this->getActivityService()->getActivity($savedActivity['id']);
        $this->assertNull($savedActivity);
    }

    public function testTriggerStart()
    {
        $savedTask = $this->handleTriggerData();
        $data = [
            'task' => $savedTask,
            'taskId' => $savedTask['id'],
        ];
        $result = $this->getActivityService()->trigger(-1, 'start', $data);
        $this->assertEquals($result, false);
        $this->getActivityService()->trigger($savedTask['activityId'], 'start', $data);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($savedTask['id']);
        $activity = $this->getActivityLearnLogService()->getLastestLearnLogByActivityIdAndUserId($savedTask['activityId'], 1);
        $this->assertEquals($activity['event'], 'start');
        $this->assertEquals($activity['learnedTime'], 0);
    }

    public function testTriggerDoing()
    {
        $savedTask = $this->handleTriggerData();
        $this->mockBiz(
            'Task:TaskService',
            [
                [
                    'functionName' => 'doTask',
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'isFinished',
                    'returnValue' => 0,
                ],
                [
                    'functionName' => 'getTimeSec',
                    'returnValue' => 200,
                ],
            ]
        );
        TimeMachine::setMockedTime(time());
        $data = [
            'task' => $savedTask,
            'taskId' => $savedTask['id'],
            'lastTime' => TimeMachine::time() - 60,
            'events' => [],
        ];

        $this->getActivityService()->trigger($savedTask['activityId'], 'doing', $data);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($savedTask['id']);
        $activity = $this->getActivityLearnLogService()->getLastestLearnLogByActivityIdAndUserId($savedTask['activityId'], 1);
        $this->assertEquals($activity['event'], 'doing');
        $this->assertEquals($activity['learnedTime'], 60);
    }

    public function testTriggerWatching()
    {
        $savedTask = $this->handleTriggerData();
        $this->mockBiz(
            'Task:TaskService',
            [
                [
                    'functionName' => 'doTask',
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'isFinished',
                    'returnValue' => 0,
                ],
                [
                    'functionName' => 'getTimeSec',
                    'returnValue' => 200,
                ],
            ]
        );
        TimeMachine::setMockedTime(time());
        $data = [
            'task' => $savedTask,
            'taskId' => $savedTask['id'],
            'lastTime' => TimeMachine::time() - 60,
            'events' => [
                'watching' => ['watchTime' => 120],
            ],
        ];

        $this->getActivityService()->trigger($savedTask['activityId'], 'doing', $data);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($savedTask['id']);
        $learnLogs = $this->getActivityLearnLogService()->search(
            ['activityId' => $savedTask['activityId'], 'userId' => 1],
            ['createdTime' => 'DESC'],
            0,
            10
        );
        $learnLogs = ArrayToolKit::index($learnLogs, 'event');
        $this->assertArrayHasKey('doing', $learnLogs);
        $this->assertEquals($learnLogs['doing']['learnedTime'], 60);
        $this->assertArrayHasKey('watching', $learnLogs);
        $this->assertEquals($learnLogs['watching']['learnedTime'], 120);
    }

    public function testTriggerFinish()
    {
        $savedTask = $this->handleTriggerData();
        TimeMachine::setMockedTime(time());
        $data = [
            'task' => $savedTask,
            'taskId' => $savedTask['id'],
            'lastTime' => TimeMachine::time() - 60,
            'events' => [
                'finish' => ['data' => ['stop' => true]],
            ],
        ];
        $this->mockBiz(
            'Task:TaskResultService',
            [
                [
                    'functionName' => 'getUserTaskResultByTaskId',
                    'returnValue' => ['id' => 1, 'status' => 'start'],
                ],
                [
                    'functionName' => 'updateTaskResult',
                    'returnValue' => [],
                ],
                [
                    'functionName' => 'getMyLearnedTimeByActivityId',
                    'returnValue' => 0,
                ],
            ]
        );

        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'recountLearningData',
                    'returnValue' => [],
                ],
                [
                    'functionName' => 'getCourse',
                    'returnValue' => ['id' => 1, 'compulsoryTaskNum' => 1],
                ],
            ]
        );

        $this->getActivityService()->trigger($savedTask['activityId'], 'doing', $data);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($savedTask['id']);
        $activity = $this->getActivityLearnLogService()->getLastestLearnLogByActivityIdAndUserId($savedTask['activityId'], 1);
        $this->assertEquals($activity['event'], 'finish');
        $this->assertEquals($activity['learnedTime'], 0);
    }

    protected function handleTriggerData()
    {
        $course = [
            'id' => 1,
            'title' => 'test',
            'courseSetId' => 1,
            'expiryMode' => 'forever',
            'learnMode' => 'lockMode',
            'isDefault' => 0,
            'status' => 'published',
            'parentId' => 0,
            'type' => 'normal',
            'rating' => 0,
            'summary' => '',
            'price' => 0,
            'courseType' => 'normal',
        ];

        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'tryManageCourse',
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'tryTakeCourse',
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'canTakeCourse',
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'getCourse',
                    'returnValue' => $course,
                ],
                [
                    'functionName' => 'updateCourseStatistics',
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'getChapter',
                    'returnValue' => ['id' => 1, 'type' => 'lesson', 'status' => 'create', 'isOptional' => 0],
                ],
            ]
        );

        $task = [
            'title' => 'test1 task',
            'mediaType' => 'text',
            'fromCourseId' => $course['id'],
            'fromCourseSetId' => 1,
            'categoryId' => 1,
        ];
        $savedTask = $this->getTaskService()->createTask($task);

        return $savedTask;
    }

    public function testSearch()
    {
        $activity1 = [
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        ];
        $savedActivity1 = $this->getActivityService()->createActivity($activity1);

        $activity2 = [
            'title' => 'test activity2',
            'mediaType' => 'text',
            'fromCourseId' => 2,
            'fromCourseSetId' => 1,
        ];
        $savedActivity2 = $this->getActivityService()->createActivity($activity2);

        $conditions = [
            'fromCourseId' => 1,
            'mediaType' => 'text',
        ];
        $activities = $this->getActivityService()->search($conditions, null, 0, 10);

        $this->assertEquals(1, count($activities));
        $this->assertArrayEquals($savedActivity1, $activities[0]);
    }

    public function testCount()
    {
        $activity1 = [
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        ];
        $savedActivity1 = $this->getActivityService()->createActivity($activity1);

        $activity2 = [
            'title' => 'test activity2',
            'mediaType' => 'text',
            'fromCourseId' => 2,
            'fromCourseSetId' => 1,
        ];
        $savedActivity2 = $this->getActivityService()->createActivity($activity2);

        $conditions = [
            'fromCourseId' => 1,
            'mediaType' => 'text',
        ];
        $count = $this->getActivityService()->count($conditions);

        $this->assertEquals(1, $count);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testPreCreateCheckWithMissingParams()
    {
        $this->getActivityService()->preCreateCheck('live', []);
    }

    /**
     * @expectedException \Biz\Activity\ActivityException
     * @expectedExceptionMessage activity.live.overlap_time_notice
     */
    public function testPreCreateCheckWithOverlapTime()
    {
        $this->mockBiz('Activity:ActivityDao', [
            ['functionName' => 'findOverlapTimeActivitiesByCourseId', 'returnValue' => 1],
        ]);
        $this->getActivityService()->preCreateCheck('live', ['fromCourseId' => 1, 'startTime' => time() + 3600, 'length' => 3]);
    }

    public function testPreCreateCheck()
    {
        $this->mockBiz('Activity:ActivityDao', [
            ['functionName' => 'findOverlapTimeActivitiesByCourseId', 'returnValue' => null],
        ]);
        $this->getActivityService()->preCreateCheck('live', ['fromCourseId' => 1, 'startTime' => time() + 3600, 'length' => 3]);
    }

    public function testPreUpdateCheck()
    {
        $this->mockBiz('Activity:ActivityDao', [
           ['functionName' => 'get', 'returnValue' => ['id' => 1, 'mediaType' => 'live']],
            ['functionName' => 'findOverlapTimeActivitiesByCourseId', 'returnValue' => null],
        ]);

        $this->getActivityService()->preUpdateCheck(1, ['fromCourseId' => 1, 'startTime' => time() + 3600, 'length' => 3]);
    }

    public function testGetActivity()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['id' => 111, 'title' => 'title'],
                    'withParams' => [1],
                ],
            ]
        );

        $activity = $this->getActivityService()->getActivity(1);

        $this->assertEquals(['id' => 111, 'title' => 'title'], $activity);
    }

    public function testGetActivityByCopyIdAndCourseSetId()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'getByCopyIdAndCourseSetId',
                    'returnValue' => ['id' => 111, 'title' => 'title'],
                    'withParams' => [1, 1],
                ],
            ]
        );

        $activity = $this->getActivityService()->getActivityByCopyIdAndCourseSetId(1, 1);

        $this->assertEquals(['id' => 111, 'title' => 'title'], $activity);
    }

    public function testFindActivities()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'findByIds',
                    'returnValue' => [[
                        'id' => 111,
                        'title' => 'title',
                        'mediaType' => 'text',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'mediaId' => 1,
                    ]],
                    'withParams' => [[1, 2]],
                ],
            ]
        );

        $activities = $this->getActivityService()->findActivities([1, 2]);

        $this->assertEquals(
            [
                'id' => 111,
                'title' => 'title',
                'mediaType' => 'text',
                'fromCourseId' => 1,
                'fromCourseSetId' => 1,
                'mediaId' => 1,
            ],
            $activities[0]
        );

        $activities = $this->getActivityService()->findActivities([1, 2], true);

        $this->assertArrayHasKey('ext', $activities[0]);
    }

    public function testFindActivitiesByCourseIdAndType()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'findActivitiesByCourseIdAndType',
                    'returnValue' => [[
                        'id' => 111,
                        'title' => 'title',
                        'mediaType' => 'text',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'mediaId' => 1,
                    ]],
                    'withParams' => [
                        1,
                        'text',
                    ],
                ],
            ]
        );

        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType(1, 'text', true);

        $this->assertEquals(111, $activities[0]['id']);
        $this->assertArrayHasKey('ext', $activities[0]);
    }

    public function testFindActivitiesByCourseSetIdAndType()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'findActivitiesByCourseSetIdAndType',
                    'returnValue' => [[
                        'id' => 111,
                        'title' => 'title',
                        'mediaType' => 'text',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'mediaId' => 1,
                    ]],
                    'withParams' => [
                        1,
                        'text',
                    ],
                ],
            ]
        );

        $activities = $this->getActivityService()->findActivitiesByCourseSetIdAndType(1, 'text', true);

        $this->assertEquals(111, $activities[0]['id']);
        $this->assertArrayHasKey('ext', $activities[0]);
    }

    public function testFindActivitiesByCourseSetIdsAndType()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'findActivitiesByCourseSetIdsAndType',
                    'returnValue' => [
                        [
                        'id' => 1,
                        'title' => 'title1',
                        'mediaType' => 'homework',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'mediaId' => 1,
                        ],
                        [
                            'id' => 2,
                            'title' => 'title2',
                            'mediaType' => 'homework',
                            'fromCourseId' => 2,
                            'fromCourseSetId' => 2,
                            'mediaId' => 1,
                        ],
                            ],
                    'withParams' => [
                        [1, 2],
                        'homework',
                    ],
                ],
            ]
        );

        $activities = $this->getActivityService()->findActivitiesByCourseSetIdsAndType([1, 2], 'homework', true);
        $this->assertCount(2, $activities);
    }

    public function testFindActivitiesByCourseIdsAndType()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'findActivitiesByCourseIdsAndType',
                    'returnValue' => [
                        [
                            'id' => 1,
                            'title' => 'title1',
                            'mediaType' => 'homework',
                            'fromCourseId' => 1,
                            'fromCourseSetId' => 1,
                            'mediaId' => 1,
                        ],
                        [
                            'id' => 2,
                            'title' => 'title2',
                            'mediaType' => 'homework',
                            'fromCourseId' => 2,
                            'fromCourseSetId' => 2,
                            'mediaId' => 1,
                        ],
                    ],
                    'withParams' => [
                        [1, 2],
                        'homework',
                    ],
                ],
            ]
        );

        $activities = $this->getActivityService()->findActivitiesByCourseIdsAndType([1, 2], 'homework', true);
        $this->assertCount(2, $activities);
    }

    public function findActivitiesByCourseSetIdsAndTypes()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'findActivitiesByCourseSetIdsAndTypes',
                    'returnValue' => [
                        [
                            'id' => 1,
                            'title' => 'title1',
                            'mediaType' => 'homework',
                            'fromCourseId' => 1,
                            'fromCourseSetId' => 1,
                            'mediaId' => 1,
                        ],
                        [
                            'id' => 2,
                            'title' => 'title2',
                            'mediaType' => 'exercise',
                            'fromCourseId' => 2,
                            'fromCourseSetId' => 2,
                            'mediaId' => 1,
                        ],
                    ],
                    'withParams' => [
                        [1, 2],
                        ['homework', 'exercise'],
                    ],
                ],
            ]
        );

        $activities = $this->getActivityService()->findActivitiesByCourseSetIdsAndTypes([1, 2], ['homework', 'exercise'], true);
        $this->assertCount(2, $activities);
    }

    public function testIsFinishedWithEndType()
    {
        $activity1 = [
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'finishType' => 'end',
            'finishData' => '',
        ];
        $savedActivity1 = $this->getActivityService()->createActivity($activity1);

        $result = $this->getActivityService()->isFinished(1);

        $this->assertFalse($result);
        $this->mockBiz('Activity:ActivityLearnLogService', [
            [
                'functionName' => 'getMyRecentFinishLogByActivityId',
                'returnValue' => [
                    [
                        'id' => 1,
                    ],
                ],
                'withParams' => [1],
            ],
        ]);

        $result = $this->getActivityService()->isFinished(1);
        $this->assertTrue($result);
    }

    public function testIsFinishedWithTimeType()
    {
        $activity1 = [
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'finishData' => '1',
        ];
        $savedActivity1 = $this->getActivityService()->createActivity($activity1);

        $result = $this->getActivityService()->isFinished(1);

        $this->assertFalse($result);
        $this->mockBiz('Task:TaskResultService', [
            [
                'functionName' => 'getMyLearnedTimeByActivityId',
                'returnValue' => 100,
                'withParams' => [1],
            ],
        ]);

        $result = $this->getActivityService()->isFinished(1);
        $this->assertTrue($result);
    }

    public function testFindActivitySupportVideoTryLook()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'findSelfVideoActivityByCourseIds',
                    'returnValue' => [[
                        'id' => 111,
                        'title' => 'title',
                        'mediaType' => 'text',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'fileId' => 1,
                    ]],
                    'withParams' => [[1, 2]],
                ],
            ]
        );

        $result = $this->getActivityService()->findActivitySupportVideoTryLook([1, 2]);

        $this->assertEquals([], $result);
    }

    public function testIsLiveFinishedActivityEmpty()
    {
        $this->mockBiz('Activity:ActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => [],
            ],
        ]);

        $this->mockBiz('Activity:LiveActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => [],
            ],
        ]);

        $result = $this->getActivityService()->isLiveFinished(1);

        $this->assertTrue($result);
    }

    public function testIsLiveFinishedThirdLiveProvider()
    {
        $startTime = time() - 3600;
        $endTime = time() - 1800;
        $this->mockBiz('Activity:ActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => ['id' => 1, 'mediaId' => 1, 'mediaType' => 'live', 'startTime' => $startTime, 'endTime' => $endTime],
            ],
        ]);
        $this->mockBiz('Activity:LiveActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => ['id' => 1, 'liveId' => '1001', 'liveProvider' => 1, 'progressStatus' => 'closed'],
            ],
        ]);

        $result = $this->getActivityService()->isLiveFinished(1);

        $this->assertTrue($result);
    }

    public function testIsLiveFinishedEsLive()
    {
        $startTime1 = time() - 3600 * 4;
        $endTime1 = time() - 3600 * 3;

        $startTime2 = time() - 3600;
        $endTime2 = time() - 1800;
        $this->mockBiz('Activity:ActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => ['id' => 1, 'mediaId' => 1, 'mediaType' => 'live', 'startTime' => $startTime1, 'endTime' => $endTime1],
            ],
            [
                'functionName' => 'get',
                'withParams' => [2],
                'returnValue' => ['id' => 2, 'mediaId' => 2, 'mediaType' => 'live', 'startTime' => $startTime2, 'endTime' => $endTime2],
            ],
            [
                'functionName' => 'get',
                'withParams' => [3],
                'returnValue' => ['id' => 3, 'mediaId' => 3, 'mediaType' => 'live', 'startTime' => $startTime2, 'endTime' => $endTime2],
            ],
        ]);

        $this->mockBiz('Activity:LiveActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => ['id' => 1, 'liveId' => '1001', 'liveProvider' => 9, 'progressStatus' => 'closed'],
            ],
            [
                'functionName' => 'get',
                'withParams' => [2],
                'returnValue' => ['id' => 2, 'liveId' => '1002', 'liveProvider' => 9, 'progressStatus' => 'closed'],
            ],
            [
                'functionName' => 'get',
                'withParams' => [3],
                'returnValue' => ['id' => 3, 'liveId' => '1003', 'liveProvider' => 9, 'progressStatus' => 'closed'],
            ],
        ]);

        $result = $this->getActivityService()->isLiveFinished(1);
        $this->assertTrue($result);

        $result = $this->getActivityService()->isLiveFinished(2);
        $this->assertTrue($result);

        $result = $this->getActivityService()->isLiveFinished(3);
        $this->assertTrue($result);
    }

    public function testCheckLiveStatusActivityEmpty()
    {
        $result = $this->getActivityService()->checkLiveStatus(1, 1);
        $this->assertFalse($result['result']);
        $this->assertEquals('message_response.live_task_not_exist.message', $result['message']);

        $this->mockBiz('Activity:ActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => ['id' => 1, 'mediaId' => 1, 'mediaType' => 'live', 'fromCourseId' => 2],
            ],
            [
                'functionName' => 'get',
                'withParams' => [2],
                'returnValue' => ['id' => 2, 'mediaId' => 2, 'mediaType' => 'live', 'fromCourseId' => 2],
            ],
            [
                'functionName' => 'get',
                'withParams' => [3],
                'returnValue' => ['id' => 3, 'mediaId' => 3, 'mediaType' => 'live', 'fromCourseId' => 2, 'startTime' => time() + 3600 * 4],
            ],
        ]);

        $result = $this->getActivityService()->checkLiveStatus(1, 1);
        $this->assertFalse($result['result']);
        $this->assertEquals('message_response.illegal_params.message', $result['message']);

        $this->mockBiz('Activity:LiveActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [2],
                'returnValue' => [],
            ],
            [
                'functionName' => 'get',
                'withParams' => [3],
                'returnValue' => ['id' => 3, 'liveId' => 1001],
            ],
        ]);

        $result = $this->getActivityService()->checkLiveStatus(2, 2);
        $this->assertFalse($result['result']);
        $this->assertEquals('message_response.live_class_not_exist.message', $result['message']);

        $result = $this->getActivityService()->checkLiveStatus(2, 3);
        $this->assertFalse($result['result']);
        $this->assertEquals('message_response.live_not_start.message', $result['message']);
    }

    public function testCheckLiveStatus()
    {
        $startTime1 = time() - 3600 * 4;
        $endTime1 = time() - 3600 * 3;

        $startTime2 = time() - 3600;
        $endTime2 = time() - 1800;
        $this->mockBiz('Activity:ActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => ['id' => 1, 'mediaId' => 1, 'fromCourseId' => 2, 'mediaType' => 'live', 'startTime' => $startTime1, 'endTime' => $endTime1],
            ],
            [
                'functionName' => 'get',
                'withParams' => [2],
                'returnValue' => ['id' => 2, 'mediaId' => 2, 'fromCourseId' => 2, 'mediaType' => 'live', 'startTime' => $startTime1, 'endTime' => $endTime1],
            ],
            [
                'functionName' => 'get',
                'withParams' => [3],
                'returnValue' => ['id' => 3, 'mediaId' => 3, 'fromCourseId' => 2, 'mediaType' => 'live', 'startTime' => $startTime2, 'endTime' => $endTime2],
            ],
        ]);

        $this->mockBiz('Activity:LiveActivityDao', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => ['id' => 1, 'liveId' => '1001', 'liveProvider' => 1, 'progressStatus' => 'created'],
            ],
            [
                'functionName' => 'get',
                'withParams' => [2],
                'returnValue' => ['id' => 2, 'liveId' => '1002', 'liveProvider' => 9, 'progressStatus' => 'closed'],
            ],
            [
                'functionName' => 'get',
                'withParams' => [3],
                'returnValue' => ['id' => 3, 'liveId' => '1003', 'liveProvider' => 9, 'progressStatus' => 'closed'],
            ],
        ]);

        $result = $this->getActivityService()->checkLiveStatus(2, 1);
        $this->assertTrue($result['result']);
        $this->assertEmpty($result['message']);

        $result = $this->getActivityService()->checkLiveStatus(2, 2);
        $this->assertFalse($result['result']);
        $this->assertEquals('message_response.live_over.message', $result['message']);

        $result = $this->getActivityService()->checkLiveStatus(2, 3);
        $this->assertFalse($result['result']);
        $this->assertEquals('message_response.live_over.message', $result['message']);
    }

    public function testGetMaterialsFromActivity()
    {
        $result1 = $this->getActivityService()->getMaterialsFromActivity(
            ['materials' => '{"id" : 1,"name" : "test.doc"}']
        );

        $this->assertEquals(1, $result1['id']);

        $result2 = $this->getActivityService()->getMaterialsFromActivity(
            ['media' => '{"id" : 1,"name" : "test.doc"}']
        );

        $this->assertEquals(1, $result2[0]['id']);
    }

    public function testFetchMedia()
    {
        $result1 = $this->getActivityService()->fetchMedia([]);

        $this->assertEquals([], $result1);

        $result2 = $this->getActivityService()->fetchMedia(['mediaId' => 1, 'mediaType' => 'text']);

        $this->assertNull($result2['ext']);
    }

    public function testFetchMedias()
    {
        $results = $this->getActivityService()->fetchMedias('text', [['mediaId' => 1]]);

        $this->assertArrayHasKey('ext', $results[0]);
    }

    public function testBuildMaterial()
    {
        $material = [
            'fileId' => 1,
            'title' => 'material',
            'summary' => 'summary',
            'link' => 'www.edusoho.com',
        ];
        $activity = [
            'fromCourseId' => 2,
            'fromCourseSetId' => 3,
            'id' => 4,
            'mediaType' => 'video',
        ];
        $result = ReflectionUtils::invokeMethod($this->getActivityService(), 'buildMaterial', [$material, $activity]);
        $this->assertEquals($result['fileId'], 1);
        $this->assertEquals($result['courseId'], 2);
        $this->assertEquals($result['courseSetId'], 3);
        $this->assertEquals($result['lessonId'], 4);
        $this->assertEquals($result['title'], 'material');
        $this->assertEquals($result['description'], 'summary');
        $this->assertEquals($result['source'], 'courseactivity');
        $this->assertEquals($result['link'], 'www.edusoho.com');
    }

    public function testDiffMaterials()
    {
        $arr1 = [
            [
                'fileId' => 0,
                'link' => 'www.edusoho.com0',
            ],
            [
                'fileId' => 1,
                'link' => 'www.edusoho.com1',
            ],
        ];
        $arr2 = [];
        $result = ReflectionUtils::invokeMethod($this->getActivityService(), 'diffMaterials', [$arr1, $arr2]);
        $this->assertEquals($result[0]['link'], 'www.edusoho.com0');

        $arr2 = [
            [
                'fileId' => 0,
                'link' => 'www.edusoho.com0',
            ],
            [
                'fileId' => 2,
                'link' => 'www.edusoho.com1',
            ],
        ];
        $result = ReflectionUtils::invokeMethod($this->getActivityService(), 'diffMaterials', [$arr1, $arr2]);
        $this->assertEquals($result[0]['fileId'], 1);
        $this->assertEquals($result[0]['link'], 'www.edusoho.com1');
    }

    public function testfindFinishedLivesWithinOneDay()
    {
        $this->mockBiz('Activity:ActivityDao', [
            [
                'functionName' => 'findFinishedLivesWithinOneDay',
                'returnValue' => [['id' => 1, 'mediaId' => 1, 'mediaType' => 'live', 'startTime' => time() - 3600, 'endTime' => time() - 1800]],
            ],
        ]);

        $results = $this->getActivityService()->findFinishedLivesWithinOneDay();

        $this->assertEquals(1, count($results));
        $this->assertEquals('live', $results[0]['mediaType']);
        $this->assertLessThan(7200, time() - $results[0]['endTime']);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }
}
