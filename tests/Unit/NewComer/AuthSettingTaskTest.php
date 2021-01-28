<?php

namespace Tests\Unit\NewComer;

use Biz\BaseTestCase;
use Biz\NewComer\AuthSettingTask;

class AuthSettingTaskTest extends BaseTestCase
{
    public function testGetStatusFalse()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['auth_setting_task' => ['status' => []]],
                ],
            ]
        );

        $task = new AuthSettingTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(false, $result);
    }

    public function testGetStatusTrue()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['auth_setting_task' => ['status' => 1]],
                ],
            ]
        );

        $task = new AuthSettingTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(true, $result);
    }
}
