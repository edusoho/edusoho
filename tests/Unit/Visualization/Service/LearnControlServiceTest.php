<?php

namespace Tests\Unit\Visualization\Service;

use Biz\BaseTestCase;
use Biz\Visualization\Service\DataCollectService;
use Biz\Visualization\Service\LearnControlService;

class LearnControlServiceTest extends BaseTestCase
{
    public function testGetUserLastLearnRecord()
    {
        $currentTime = time();
        $record = $this->getDataCollectService()->push([
            'userId' => 1,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'event' => 'start',
            'client' => 'pc',
            'startTime' => $currentTime,
            'endTime' => $currentTime,
            'duration' => 0,
            'mediaType' => 'text',
            'flowSign' => 'test12345',
            'data' => [
                'userAgent' => 'PC',
            ],
        ]);
        $lastRecord = $this->getLearnControlService()->getUserLastLearnRecord(1);
        self::assertEquals($record, $lastRecord);
    }

    public function testGetUserLastLearnRecordBySign()
    {
        $currentTime = time();
        $record = $this->getDataCollectService()->push([
            'userId' => 1,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'event' => 'start',
            'client' => 'pc',
            'startTime' => $currentTime,
            'endTime' => $currentTime,
            'duration' => 0,
            'mediaType' => 'text',
            'flowSign' => 'test12345',
            'data' => [
                'userAgent' => 'PC',
            ],
        ]);

        $lastRecord = $this->getLearnControlService()->getUserLastLearnRecordBySign(1, 'test12345');
        self::assertEquals($record, $lastRecord);
    }

    public function testGetUserLatestActiveFlow()
    {
        $flow1 = $this->getDataCollectService()->createLearnFlow(1, 1, 'test123');
        $latestFlow = $this->getLearnControlService()->getUserLatestActiveFlow(1);
        self::assertEquals($flow1, $latestFlow);
    }

    public function testCheckActive()
    {
        $flow1 = $this->getDataCollectService()->createLearnFlow(1, 1, 'test123');
        $result = $this->getLearnControlService()->checkActive(1, 'test123');

        self::assertEquals(1, $flow1['active']);
        self::assertTrue($result[0]);
    }

    public function testCheckCreateNewFlow()
    {
    }

    public function testFreshFlow()
    {
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => [
                'multiple_learn_enable' => 0,
            ]],
        ]);
        $flow1 = $this->getDataCollectService()->createLearnFlow(1, 1, 'test123');
        $flow2 = $this->getDataCollectService()->createLearnFlow(1, 2, 'test456');
        self::assertEquals(1, $flow1['active']);
        self::assertEquals(1, $flow2['active']);

        $this->getLearnControlService()->freshFlow(1, 'test456');
        $result1 = $this->getDataCollectService()->getFlowBySign(1, 'test123');
        $result2 = $this->getDataCollectService()->getFlowBySign(1, 'test456');
        self::assertEquals(0, $result1['active']);
        self::assertEquals(1, $result2['active']);
    }

    /**
     * @return DataCollectService
     */
    protected function getDataCollectService()
    {
        return $this->getBiz()->service('Visualization:DataCollectService');
    }

    /**
     * @return LearnControlService
     */
    protected function getLearnControlService()
    {
        return $this->getBiz()->service('Visualization:LearnControlService');
    }
}
