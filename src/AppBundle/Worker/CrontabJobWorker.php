<?php

namespace AppBundle\Worker;

use Codeages\Plumber\Queue\Job;

class CrontabJobWorker extends BaseWorker
{
    public function doExecute(Job $job)
    {
        $body = json_decode($job->getBody(), true);

        $this->logger->info("JobWorker:execute crontab job job #{$body['id']}, priority: {$job->getPriority()} body: {$job->getBody()}");

        return $this->execCronTabJob();
    }

    protected function execCronTabJob()
    {
        $root_dir = __DIR__.'/../../../';
        $this->exec_shell("cd {$root_dir}");

        return $this->exec_shell('app/console util:scheduler');
    }

    protected function exec_shell($cmd)
    {
        $process = proc_open(
            $cmd,
            [
                ['pipe', 'r'],
                ['pipe', 'w'],
                ['pipe', 'w'],
            ],
            $pipes
        );

        proc_close($process);

        return true;
    }
}
