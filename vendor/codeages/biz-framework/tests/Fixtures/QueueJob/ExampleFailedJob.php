<?php
namespace Tests\Fixtures\QueueJob;

use Codeages\Biz\Framework\Queue\AbstractJob;

class ExampleFailedJob extends AbstractJob
{
    public function execute()
    {
        $this->biz['logger']->info("ExampleFailedJob executed.");
        return [self::FAILED, "ExampleFailedJob execute failed."];
    }
}