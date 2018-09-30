<?php

namespace Tests\Unit\TableIndex\Service;

use Biz\BaseTestCase;

class DeleteExpiredTokenJobTest extends BaseTestCase
{
    public function testRegister()
    {
        $this->getTableIndexService()->register();
        $jobs = $this->getSchedulerService()->searchJobs(array(), array(), 0, PHP_INT_MAX);
        $this->assertEquals('AddTableIndexJob', $jobs[0]['name']);
    }

    protected function getSchedulerService()
    {
        return $this->createservice('Scheduler:SchedulerService');
    }

    protected function getTableIndexService()
    {
        return $this->createservice('TableIndex:TableIndexService');
    }
}
