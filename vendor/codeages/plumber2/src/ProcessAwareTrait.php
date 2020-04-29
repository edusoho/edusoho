<?php

namespace Codeages\Plumber;

use Swoole\Process;

/**
 * Basic Implementation of ProcessAwareInterface.
 */
trait ProcessAwareTrait
{
    /**
     * The process instance.
     *
     * @var Process
     */
    protected $process;

    /**
     * Sets a process.
     *
     * @param Process $process
     */
    public function setProcess(Process $process)
    {
        $this->process = $process;
    }
}
