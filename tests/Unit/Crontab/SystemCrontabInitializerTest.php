<?php

namespace Tests\Unit\Crontab;

use Biz\BaseTestCase;
use Biz\Crontab\SystemCrontabInitializer;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class SystemCrontabInitializerTest extends BaseTestCase
{
    public function testInit()
    {
        SystemCrontabInitializer::init();

        $crontabJobs = SystemCrontabInitializer::findCrontabJobs();

        $this->assertCount(10, $crontabJobs);

        $this->assertGreaterThanOrEqual(1, $this->getSchedulerService()->countJobs(array()));
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }
}
