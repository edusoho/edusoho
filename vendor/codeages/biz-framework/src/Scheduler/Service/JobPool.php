<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

use Codeages\Biz\Framework\Scheduler\Job;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class JobPool
{
    private $options = array();
    private $biz;

    const SUCCESS = 'success';
    const POOL_FULL = 'pool_full';

    public function __construct($biz)
    {
        $this->biz = $biz;
        $this->options = $biz['scheduler.options'];
    }

    public function execute(Job $job)
    {
        if ($this->isFull($job)) {
            return static::POOL_FULL;
        }

        $result = '';
        try {
            $result = $job->execute();
        } catch (\Exception $e) {
            $this->release($job);
            throw $e;
        }

        $this->release($job);

        if (empty($result)) {
            return static::SUCCESS;
        }

        return $result;
    }

    public function getJobPool($name = 'default')
    {
        return $this->getJobPoolDao()->getByName($name);
    }

    public function release($job)
    {
        $jobPool = $this->getJobPool($job['pool']);

        $lockName = "job_pool.{$jobPool['name']}";
        $this->biz['lock']->get($lockName, 10);

        $this->wavePoolNum($jobPool['id'], -1);

        $this->biz['lock']->release($lockName);
    }

    protected function isFull($job)
    {
        $options = array_merge($this->options, array('name' => $job['pool']));

        if (!empty($this->biz["scheduler.job.pool.{$job['pool']}.options"])) {
            $options = array_merge($options, $this->biz["scheduler.job.pool.{$job['pool']}.options"]);
        }

        $lockName = "job_pool.{$options['name']}";
        $this->biz['lock']->get($lockName, 10);

        $jobPool = $this->getJobPool($options['name']);
        if (empty($jobPool)) {
            $jobPool = ArrayToolkit::parts($options, array('max_num', 'num', 'name', 'timeout'));
            $jobPool = $this->getJobPoolDao()->create($jobPool);
        }

        if ($jobPool['num'] == $jobPool['max_num']) {
            $this->biz['lock']->release($lockName);

            return true;
        }

        $this->wavePoolNum($jobPool['id'], 1);

        $this->biz['lock']->release($lockName);

        return false;
    }

    protected function wavePoolNum($id, $diff)
    {
        $ids = array($id);
        $diff = array('num' => $diff);
        $jobPool = $this->getJobPoolDao()->get($id);
        if (!(0 == $jobPool['num'] && $diff['num'] < 0)) {
            $this->getJobPoolDao()->wave($ids, $diff);
        }
    }

    protected function getJobPoolDao()
    {
        return $this->biz->dao('Scheduler:JobPoolDao');
    }
    
    public function __get($name)
    {
        return empty($this->data[$name]) ? '' : $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
}
