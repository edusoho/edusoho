<?php

namespace Biz\Crontab\Service\Impl;

use Biz\Crontab\Service\Job;

class EmptyJob implements Job
{
    public function execute($params)
    {
        echo "\ntest job has been executed\n";
    }
}
