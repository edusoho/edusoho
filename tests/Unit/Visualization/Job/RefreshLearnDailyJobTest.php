<?php

namespace Tests\Unit\Visualization\Job;

use Biz\BaseTestCase;
use Biz\Visualization\Job\RefreshLearnDailyJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class RefreshLearnDailyJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $mockedService = $this->mockBiz('System:CacheService', [
            ['functionName' => 'set'],
            ['functionName' => 'clear', 'withParams' => ['refresh_learn_daily']],
        ]);

        $before = $this->getSchedulerService()->countJobs(['name' => 'refresh']);

        $job = new RefreshLearnDailyJob([], $this->biz);
        $job->execute();
        $result = $this->getSchedulerService()->countJobs(['name' => 'RefreshLearnDailyJob']);

        $this->assertEquals(0, $before);
        $this->assertEquals(4, $result);
        $mockedService->shouldHaveReceived('set')->times(4);
        $mockedService->shouldHaveReceived('clear')->times(1);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
