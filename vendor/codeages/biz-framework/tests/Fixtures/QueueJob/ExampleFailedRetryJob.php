<?php

namespace Tests\Fixtures\QueueJob;

use Codeages\Biz\Framework\Queue\AbstractJob;

class ExampleFailedRetryJob extends AbstractJob
{
    public function execute()
    {
        $this->biz['logger']->info('ExampleFailedRetryJob executed.');

        return array(self::FAILED_RETRY, 'ExampleFailedRetryJob execute failed, try again.');
    }
}
