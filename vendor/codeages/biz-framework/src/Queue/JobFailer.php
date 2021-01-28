<?php

namespace Codeages\Biz\Framework\Queue;

class JobFailer
{
    protected $dao;

    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    public function log(Job $job, $queue, $message)
    {
        $failedJob = array(
            'queue' => $queue,
            'body' => $job->getBody(),
            'class' => $job->getMetadata('class'),
            'timeout' => $job->getMetadata('timeout', 0),
            'priority' => $job->getMetadata('priority', 0),
            'reason' => $message,
        );

        $this->dao->create($failedJob);
    }
}
