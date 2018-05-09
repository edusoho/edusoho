<?php

namespace Tests\Unit\Activity\Listener;

use Biz\BaseTestCase;
use Biz\Activity\Listener\VideoActivityWatchListener;

class VideoActivityWatchListenerTest extends BaseTestCase
{
    public function testHandleEmpty()
    {
        $listener = new VideoActivityWatchListener($this->getBiz());
        $result = $listener->handle(array('mediaType' => 'video', 'mediaId' => 1), array());
        $this->assertNull($result);
    }

    public function testHandleSuccess()
    {
        $xpiService = $this->mockBiz('Xapi:XapiService', array(
            array(
                'functionName' => 'watchTask',
                'returnValue' => array(),
            ),
        ));

        $taskService = $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTimeSec',
                'returnValue' => 10,
            ),
            array(
                'functionName' => 'watchTask',
                'returnValue' => array(),
            ),
        ));
        
        $listener = new VideoActivityWatchListener($this->getBiz());
        $result = $listener->handle(array('id' => 1, 'mediaType' => 'video', 'mediaId' => 1), array('task' => array('id' => 1), 'watchTime' => 20));

        $xpiService->shouldHaveReceived('watchTask')->times(1);
        $taskService->shouldHaveReceived('watchTask')->times(1);
    }
}
