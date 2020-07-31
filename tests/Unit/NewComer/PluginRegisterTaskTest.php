<?php

namespace Tests\Unit\NewComer;

use Biz\BaseTestCase;
use Biz\NewComer\PluginRegisterTask;

class PluginRegisterTaskTest extends BaseTestCase
{
    public function testGetStatusFalseByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['plugin_register_task' => ['status' => []]],
                ],
            ]
        );

        $task = new PluginRegisterTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(false, $result);
    }

    public function testGetStatusTrueByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['plugin_register_task' => ['status' => 1]],
                ],
            ]
        );

        $task = new PluginRegisterTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(true, $result);
    }

    public function testGetStatusTrueByApps()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['plugin_register_task' => []],
                ],
                [
                    'functionName' => 'set',
                    'returnValue' => null,
                ],
            ]
        );

        $this->mockBiz('CloudPlatform:AppService',
            [
                [
                    'functionName' => 'findApps',
                    'returnValue' => [
                            ['type' => 'plugin'],
                        ],
                ],
                [
                    'functionName' => 'findAppCount',
                    'returnValue' => [],
                ],
            ]
        );

        $count = new PluginRegisterTask($this->getBiz());
        $result = $count->getStatus();

        $this->assertEquals(true, $result);
    }
}
