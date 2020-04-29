<?php

namespace AppBundle\Worker;

use Codeages\Plumber\Queue\Job;

class CrontabJobWorker extends BaseWorker
{
    public function doExecute(Job $job)
    {
        $data = json_decode($job->getBody(), true);
        $this->logger->info("execute crontab job job #{$job->getId()}, body: ".json_encode($data));
        try {
            $res = $this->execCronTabJob($data['options']);
        } catch (\Exception $e) {
            $this->logger->info('execute crontab job failed'.$e->getMessage().',traceString:'.$e->getTraceAsString());

            return self::FINISH;
        }

        if ($res) {
            return self::FINISH;
        }
    }

    protected function execCronTabJob($dbConfig)
    {
        if (empty($dbConfig)) {
            return true;
        }

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
