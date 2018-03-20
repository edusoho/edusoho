<?php

namespace Codeages\Biz\Framework\Queue\Driver;

use Codeages\Biz\Framework\Queue\Job;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Queue\JobFailer;

class SyncQueue extends AbstractQueue implements Queue
{
    protected $jobs = array();

    protected $failer;

    public function __construct($name, Biz $biz, JobFailer $failer = null, $options = array())
    {
        $this->failer = $failer;
        parent::__construct($name, $biz, $options);
    }

    public function push(Job $job)
    {
        $job->setId(uniqid());
        $job->setMetadata('class', get_class($job));

        if (!empty($this->options['async_execute'])) {
            $this->jobs[$job->getId()] = $job;

            return;
        }
        $job->setBiz($this->biz);
        $result = $job->execute();

        if (is_array($result)) {
            $result = array_values($result);
            $code = isset($result[0]) ? $result[0] : null;
            $message = isset($result[1]) ? $result[1] : '';
        } else {
            $code = $result;
            $message = '';
        }

        if (Job::FAILED == $code || Job::FAILED_RETRY == $code) {
            $this->failer && $this->failer->log($job, $this->getName(), $message);
        }
    }

    public function pop(array $options = array())
    {
        $job = array_shift($this->jobs);
        if (empty($job)) {
            return null;
        }
        $job->setBiz($this->biz);

        return $job;
    }

    public function delete(Job $job)
    {
        unset($this->jobs[$job->getId()]);
    }

    public function release(Job $job, array $options = array())
    {
        // 啥都不做
    }
}
