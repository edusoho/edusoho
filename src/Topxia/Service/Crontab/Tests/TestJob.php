<?php
namespace Topxia\Service\Crontab\Tests;

use Topxia\Service\Crontab\Job;

class TestJob implements Job
{
    public function execute($params)
    {
        // echo "\ntest job has been executed\n";
    }
}
