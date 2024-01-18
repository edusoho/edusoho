<?php

namespace Tests\Unit\Activity\Service;

use Biz\BaseTestCase;

class ActivityLearnLogServiceTest extends BaseTestCase
{
    public function testCreateLog()
    {
        $activity = ['id' => 11, 'mediaType' => 'text'];
        $data = ['lastTime' => 1111111, 'watchedTime' => 2222222, 'task' => ['id' => 1]];
        $log1 = $this->getActivityLearnLogService()->createLog($activity, 'test', $data);

        $this->assertEquals(11, $log1['activityId']);

        $data = ['lastTime' => 1111111, 'watchedTime' => 2222222, 'taskId' => 22];
        $log2 = $this->getActivityLearnLogService()->createLog($activity, 'test', $data);

        $this->assertEquals(22, $log2['courseTaskId']);

        $data = ['lastTime' => 1111111, 'watchedTime' => 2222222];
        $log3 = $this->getActivityLearnLogService()->createLog($activity, 'test', $data);

        $this->assertEquals(0, $log3['courseTaskId']);
    }

    public function testGetMyRecentFinishLogByActivityId()
    {
        $activity = ['id' => 11, 'mediaType' => 'text'];
        $data = ['lastTime' => 1111111, 'watchedTime' => 2222222];
        $log = $this->getActivityLearnLogService()->createLog($activity, 'finish', $data);
        $result = $this->getActivityLearnLogService()->getMyRecentFinishLogByActivityId(11);

        $this->assertEquals(11, $result[0]['activityId']);
    }

    public function testCalcLearnProcessByCourseIdAndUserId()
    {
        $this->mockBiz(
            'Activity:ActivityLearnLogDao',
            [
                [
                    'functionName' => 'countLearnedDaysByActivityIdsAndUserId',
                    'returnValue' => 10,
                    'withParams' => [[4], 24],
                ],
            ]
        );
        $this->mockBiz(
            'Activity:ActivityDao',
            [
                [
                    'functionName' => 'findByCourseId',
                    'returnValue' => [
                        0 => [
                            'id' => '4',
                        ],
                    ],
                    'withParams' => [24],
                ],
            ]
        );

        $result = $this->getActivityLearnLogService()->calcLearnProcessByCourseIdAndUserId(24, 24);

        $this->assertEquals(10, $result[0]);
    }

    public function testDeleteLearnLogsByActivityId()
    {
        $this->mockBiz(
            'Activity:ActivityLearnLogDao',
            [
                [
                    'functionName' => 'deleteByActivityId',
                    'returnValue' => ['id' => 111, 'event' => 'finish'],
                    'withParams' => [33],
                ],
                [
                    'functionName' => 'count',
                    'returnValue' => 500,
                    'withParams' => [['activityId' => 33]],
                ],
            ]
        );

        $result = $this->getActivityLearnLogService()->deleteLearnLogsByActivityId(33);

        $this->assertEquals(['id' => 111, 'event' => 'finish'], $result);
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    protected function getActivityLearnLogDao()
    {
        return $this->createDao('Activity:ActivityLearnLogDao');
    }
}
