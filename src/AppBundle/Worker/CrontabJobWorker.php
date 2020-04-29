<?php

namespace AppBundle\Worker;

use Codeages\Plumber\Queue\Job;

class CrontabJobWorker extends BaseWorker
{
    public function doExecute(Job $job)
    {
        $this->logger->info("execute crontab job job #{$job->getId()}, body: {$job->getBody()}");
        try {
            $res = $this->execCronTabJob();
        } catch (\Exception $e) {
            $this->logger->info('execute crontab job failed'.$e->getMessage().',traceString:'.$e->getTraceAsString());

            return self::FINISH;
        }

        if ($res) {
            return self::FINISH;
        }
    }

    protected function execCronTabJob()
    {
        $root_dir = __DIR__.'/../../../';
        $this->exec_shell("cd {$root_dir}");

        return $this->exec_shell('app/console util:scheduler exec');
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

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
