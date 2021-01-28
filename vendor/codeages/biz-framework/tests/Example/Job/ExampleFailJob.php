<?php

namespace Tests\Example\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ExampleFailJob extends AbstractJob
{
    public function execute()
    {
        $i = 0;
        ++$i;

        return static::FAILURE;
    }
}
