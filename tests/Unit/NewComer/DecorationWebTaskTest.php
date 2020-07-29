<?php


namespace Tests\Unit\NewComer;

use Biz\NewComer\DecorationWebTask;
use Biz\BaseTestCase;

class DecorationWebTaskTest extends BaseTestCase
{
    public function testGetStatusFalseByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' =>
                        ['decoration_web_task' => ['status' => []]]
                ],
                [
                    'functionName' => 'set',
                    'returnValue' => []
                ]
            ]
        );

        $task = new DecorationWebTask($this->getBiz());
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
                        ['decoration_web_task' => ['status' => 1]]
                ],
                [
                    'functionName' => 'set',
                    'returnValue' => []
                ]
            ]
        );

        $task = new DecorationWebTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(true, $result);
    }


}