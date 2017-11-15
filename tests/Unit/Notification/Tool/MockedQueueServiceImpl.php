<?php

namespace Tests\Unit\Notification\Tool;

use Codeages\Biz\Framework\Queue\Job;

class MockedQueueServiceImpl
{
    private $job;
    
    public function pushJob(Job $job, $queue = null)
    {
        $this->job = $job;
    }

    public function getJob()
    {
        return $this->job;
    }
}
