<?php

namespace Tests\Unit\Activity\Listener;

use Biz\BaseTestCase;
use Biz\Activity\Listener\LiveActivityWatchListener;

class LiveActivityWatchListenerTest extends BaseTestCase
{
    public function testHandle()
    {
        $listener = new LiveActivityWatchListener($this->getBiz());
        $result = $listener->handle(array(), array('task' => array()));
        $this->assertNull($result);

        $taskService = $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTimeSec',
                'returnValue' => 60
            ),
            array(
                'functionName' => 'watchTask',
                'returnValue' => array()
            ),
        ));

        $xpiService = $this->mockBiz('Xapi:XapiService', array(
            array(
                'functionName' => 'watchTask',
                'returnValue' => array()
            ),
        ));

        $listener = new LiveActivityWatchListener($this->getBiz());
        $result = $listener->handle(array(), array('task' => array('id' => 10),'watchTime' => 10));

        $taskService->shouldHaveReceived('getTimeSec')->times(1);
        $taskService->shouldHaveReceived('watchTask')->times(1);
        $xpiService->shouldHaveReceived('watchTask')->times(1);
    }
}