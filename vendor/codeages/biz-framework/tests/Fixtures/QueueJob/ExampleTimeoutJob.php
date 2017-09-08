<?php

namespace Tests\Fixtures\QueueJob;

use Codeages\Biz\Framework\Queue\AbstractJob;

class ExampleTimeoutJob extends AbstractJob
{
    public function execute()
    {
        $i = 0;
        while (true) {
            ++$i;
            // echo $i;
            // sleep(1);
        }
    }
}
