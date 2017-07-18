<?php

namespace Tests\Unit\Crontab;

use Biz\BaseTestCase;
use Biz\Crontab\SystemCrontabInitializer;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use TiBeN\CrontabManager\CrontabAdapter;
use TiBeN\CrontabManager\CrontabRepository;

class SystemCrontabInitializerTest extends BaseTestCase
{
    public function testInit()
    {
        SystemCrontabInitializer::init();

        $crontabRepository = new CrontabRepository(new CrontabAdapter());

        $crontabJobs = $crontabRepository->findJobByRegex(SystemCrontabInitializer::SCHEDULER_COMMAND_PATTERN);

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
