<?php

namespace AppBundle\Worker;

use Codeages\Plumber\Queue\Job;

class ExampleWorker extends BaseWorker
{
    public function doExecute(Job $job)
    {
        file_put_contents('/var/www/edusoho/app/logs/example.log', "execute job #{$job->getId()}, body: {$job->getBody()}", FILE_APPEND);

        $this->logger->info("execute job #{$job->getId()}, body: {$job->getBody()}");

        return self::FINISH;
    }
}
