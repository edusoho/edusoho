<?php

namespace Tests\Unit\Activity\Listener;

use Biz\BaseTestCase;
use Biz\Activity\Listener\TestpaperActivityCreateListener;

class TestpaperActivityCreateListenerTest extends BaseTestCase
{
    public function testHandleEmpty()
    {
        $listener = new TestpaperActivityCreateListener($this->getBiz());
        $result = $listener->handle(array('mediaType' => 'homework', 'mediaId' => 1), array());
        $this->assertNull($result);

        $taskService = $this->mockBiz('Activity:TestpaperActivityService', array(
            array(
                'functionName' => 'getActivity',
                'withParams' => array(3),
                'returnValue' => array('limitedTime' => 0)
            ),
            array(
                'functionName' => 'getActivity',
                'withParams' => array(4),
                'returnValue' => array('limitedTime' => 360, 'testMode' => 'normal')
            ),
            array(
                'functionName' => 'getActivity',
                'withParams' => array(5),
                'returnValue' => array('limitedTime' => 360, 'testMode' => 'realTime')
            ),
        ));

        $listener = new TestpaperActivityCreateListener($this->getBiz());
        $result = $listener->handle(array('mediaType' => 'testpaper', 'mediaId' => 3), array());
        $this->assertNull($result);

        $listener = new TestpaperActivityCreateListener($this->getBiz());
        $result = $listener->handle(array('mediaType' => 'testpaper', 'mediaId' => 4), array());
        $this->assertNull($result);

        $listener = new TestpaperActivityCreateListener($this->getBiz());
        $result = $listener->handle(array('mediaType' => 'testpaper', 'mediaId' => 5), array());
        $this->assertNull($result);
    }

    public function testHandleSuccess()
    {
        $taskService = $this->mockBiz('Activity:TestpaperActivityService', array(
            array(
                'functionName' => 'getActivity',
                'withParams' => array(6),
                'returnValue' => array('limitedTime' => 30, 'testMode' => 'realTime')
            ),
        ));
        $schedulerService = $this->mockBiz('Scheduler:SchedulerService', array(
            array(
                'functionName' => 'register',
                'returnValue' => array()
            )
        ));
        $listener = new TestpaperActivityCreateListener($this->getBiz());
        $result = $listener->handle(array('id' => 1,'mediaType' => 'testpaper', 'mediaId' => 6, 'startTime' => time() + 3600), array());

        $schedulerService->shouldHaveReceived('register')->times(1);
    }
}