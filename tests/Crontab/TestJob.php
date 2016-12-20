<?php
namespace Tests\Crontab;

use Biz\Crontab\Service\Job;

class TestJob implements Job
{
    public function execute($params)
    {
        // echo "\ntest job has been executed\n";
    }
}
