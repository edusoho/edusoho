<?php

namespace Tests\Unit\Activity\Listener;

use Biz\Activity\Listener\VideoActivityWatchListener;
use Biz\BaseTestCase;

class VideoActivityWatchListenerTest extends BaseTestCase
{
    public function testHandleEmpty()
    {
        $listener = new VideoActivityWatchListener($this->getBiz());
        $result = $listener->handle(['mediaType' => 'video', 'mediaId' => 1], []);
        $this->assertNull($result);
    }

    public function testHandleSuccess()
    {
        $listener = new VideoActivityWatchListener($this->getBiz());
        $result = $listener->handle(['id' => 1, 'mediaType' => 'video', 'mediaId' => 1], ['task' => ['id' => 1], 'watchTime' => 20]);
    }
}
