<?php

namespace Tests\Example\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ExampleJob extends AbstractJob
{
    public function execute()
    {
        $i = 0;
        ++$i;
    }
}
