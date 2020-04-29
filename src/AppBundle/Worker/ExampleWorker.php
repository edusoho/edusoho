<?php

namespace AppBundle\Worker;

use Codeages\Plumber\Queue\Job;

class ExampleWorker extends BaseWorker
{
    public function doExecute(Job $job)
    {
        $this->logger->info("execute job #{$job->getId()}, body: {$job->getBody()}");

        return self::FINISH;
    }
}
