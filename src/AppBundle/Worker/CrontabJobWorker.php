<?php

namespace AppBundle\Worker;

use Codeages\Plumber\Queue\Job;

class CrontabJobWorker extends BaseWorker
{
    public function doExecute(Job $job)
    {
        $body = json_decode($job->getBody(), true);

        $this->logger->info("JobWorker:execute crontab job job #{$body['id']}, priority: {$job->getPriority()} body: {$job->getBody()}");
        $biz = $this->getBiz();

        exec("{$biz['kernel.root_dir']}/../app/console util:scheduler");

        return true;
    }
}
