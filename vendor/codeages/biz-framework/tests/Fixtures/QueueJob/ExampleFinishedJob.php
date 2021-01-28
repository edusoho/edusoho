<?php

namespace Tests\Fixtures\QueueJob;

use Codeages\Biz\Framework\Queue\AbstractJob;

class ExampleFinishedJob extends AbstractJob
{
    public function execute()
    {
        $this->biz['logger']->info('ExampleFinishedJob executed.');
    }
}
