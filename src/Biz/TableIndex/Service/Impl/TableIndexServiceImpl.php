<?php

namespace Biz\TableIndex\Service\Impl;

use Biz\BaseService;
use Biz\TableIndex\Service\TableIndexService;

class TableIndexServiceImpl extends BaseService implements TableIndexService
{
    public function register()
    {
        $time = time();
        $today = strtotime(date('Y-m-d', $time).'02:00:00');

        if ($time > $today) {
            $time = strtotime(date('Y-m-d', strtotime('+1 day')).'02:00:00');
        }
        $job = array(
            'name' => 'AddTableIndexJob',
            'expression' => $time,
            'class' => 'Biz\TableIndex\Job\AddTableIndexJob',
            'args' => array(),
            'misfire_policy' => 'executing',
        );

        $this->getSchedulerService()->register($job);
    }

    protected function getSchedulerService()
    {
        return $this->createservice('Scheduler:SchedulerService');
    }
}
