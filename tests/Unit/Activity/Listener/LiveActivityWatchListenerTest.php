<?php

namespace Tests\Unit\Activity\Listener;

use Biz\Activity\Listener\LiveActivityWatchListener;
use Biz\BaseTestCase;

class LiveActivityWatchListenerTest extends BaseTestCase
{
    public function testHandle()
    {
        $listener = new LiveActivityWatchListener($this->getBiz());
        $result = $listener->handle([], ['task' => []]);
        $this->assertNull($result);
    }
}
