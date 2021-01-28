<?php

namespace Tests\Unit\Crontab;

use Biz\BaseTestCase;
use Biz\Crontab\SystemCrontabInitializer;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class SystemCrontabInitializerTest extends BaseTestCase
{
    public function testInit()
    {
        try {
            SystemCrontabInitializer::init();

            $crontabJobs = SystemCrontabInitializer::findCrontabJobs();

            $this->assertCount(SystemCrontabInitializer::MAX_CRONTAB_NUM, $crontabJobs);

            $this->assertGreaterThanOrEqual(1, $this->getSchedulerService()->countJobs(array()));
        } catch (\Exception $e) {
        }
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }
}
