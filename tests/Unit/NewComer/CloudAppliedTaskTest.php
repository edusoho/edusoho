<?php


namespace Tests\Unit\NewComer;

use Biz\BaseTestCase;
use Biz\NewComer\CloudAppliedTask;

class CloudAppliedTaskTest extends BaseTestCase
{
    public function testGetStatusFalseByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                'functionName' => 'get',
                'returnValue' =>
                    ['cloud_applied_task' => ['status' => []]]
                ]
            ]
        );

        $task = new CloudAppliedTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(false, $result);
    }

    public function testGetStatusTrueByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                'functionName' => 'get',
                'returnValue' =>
                    ['cloud_applied_task' => ['status' => 1]]
                ]
            ]
        );

        $task = new CloudAppliedTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(true, $result);
    }

    public function testGetStatusTrueByStorage()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                'functionName' => 'get',
                'returnValue' =>
                    ['cloud_key_applied' => 1]
                ],
                [
                'functionName' => 'set',
                'returnValue' => null
                ]
            ]
        );

        $storage = new CloudAppliedTask($this->getBiz());
        $result = $storage->getStatus();

        $this->assertEquals(true, $result);

    }


}