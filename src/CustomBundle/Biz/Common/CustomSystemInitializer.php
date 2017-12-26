<?php

namespace CustomBundle\Biz\Common;

class CustomSystemInitializer
{
    protected $biz;
    protected $output;

    public function __construct($biz, $output)
    {
        $this->biz = $biz;
        $this->output = $output;
    }

    public function init()
    {
        // $this->output->write('  初始化定制的数据  ');
        // $this->initData();
        // $this->initJob();
        // $this->output->writeln(' ...<info>成功</info>');
    }

    private function initData()
    {
        $db = $this->biz['db'];
    }

    private function initJob()
    {
        $source = 'Custom';

        $jobMap = array(
            'jobName' => array(
                'expression' => '* * * * *',
                'class' => 'tta',
            ),
        );
        $defaultJob = array(
            'pool' => 'default',
            'source' => $source,
            'args' => array(),
        );

        foreach ($jobMap as $key => $job) {
            $count = $this->getSchedulerService()->countJobs(array('name' => $key, 'source' => $source));
            if (0 == $count) {
                $job = array_merge($defaultJob, $job);
                $job['name'] = $key;
                $this->getSchedulerService()->register($job);
            }
        }
    }

    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    private function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
