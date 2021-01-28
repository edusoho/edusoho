<?php

namespace Codeages\Plumber;

use Codeages\Plumber\Queue\Job;

interface WorkerInterface
{
    /**
     * Worker执行返回码：执行成功
     */
    const FINISH = 'finish';

    /**
     * Worker执行返回码：重试
     */
    const RETRY = 'retry';

    /**
     * Worker执行返回码：搁置.
     */
    const BURY = 'bury';

    public function execute(Job $job);
}
