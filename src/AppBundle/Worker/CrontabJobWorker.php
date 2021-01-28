<?php

namespace AppBundle\Worker;

use Codeages\Plumber\Queue\Job;

class CrontabJobWorker extends BaseWorker
{
    public function doExecute(Job $job)
    {
        $biz = $this->getBiz();

        exec("{$biz['kernel.root_dir']}/../app/console util:scheduler");

        return true;
    }
}
