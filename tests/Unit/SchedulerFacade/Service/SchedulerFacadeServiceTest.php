<?php

namespace Tests\Unit\SchedulerFacade\Service;

use Biz\BaseTestCase;
use Biz\SchedulerFacade\Service\SchedulerFacadeService;

class SchedulerFacadeServiceTest extends BaseTestCase
{
    public function testSetNextFiredTime()
    {
        $job = $this->getSchedulerFacadeService()->register(array(
            'name' => 'testJob',
            'source' => 'MAIN',
            'expression' => intval(time() + 10),
            'class' => 'Biz\Test\Job\TestJob', //无实体文件
            'args' => array('cursor' => 0),
            'misfire_threshold' => 60 * 60,
        ));

        $nextFiredTime = time() + 100;
        $newJob = $this->getSchedulerFacadeService()->setNextFiredTime($job['id'], $nextFiredTime);
        $this->assertEquals($nextFiredTime, $newJob['next_fire_time']);
    }

    public function testSetNextFiredTimeWhenEmptyJob()
    {
        $nextFiredTime = time() + 100;
        $newJob = $this->getSchedulerFacadeService()->setNextFiredTime(100, $nextFiredTime);
        $this->assertNull($newJob);
    }

    /**
     * @return SchedulerFacadeService
     */
    protected function getSchedulerFacadeService()
    {
        return $this->biz->service('SchedulerFacade:SchedulerFacadeService');
    }
}
