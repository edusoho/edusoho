<?php

namespace Biz\Crontab\Service\Impl;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class EmptyJob extends AbstractJob
{
    public function execute()
    {
    }
}
