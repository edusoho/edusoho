<?php

namespace Tests\Unit\Visualization\Job;

use Biz\BaseTestCase;
use Biz\Visualization\Job\RefreshCoursePlanLearnDailyJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class RefreshCoursePlanLearnDailyJobTest extends BaseTestCase
{
    public function testExecuteWithSettingByPlaying()
    {
        $mockedCacheService = $this->mockBiz('System:CacheService', [
            ['functionName' => 'clear', 'withParams' => ['refresh_learn_daily']],
        ]);
        $mockedSettingService = $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'withParams' => ['videoEffectiveTimeStatistics'], 'returnValue' => ['statistical_dimension' => 'playing']],
        ]);

        $mockedJobDao = $this->mockBiz('Visualization:CoursePlanLearnDailyDao', [
            ['functionName' => 'batchUpdate'],
        ]);

        $job = new RefreshCoursePlanLearnDailyJob([], $this->biz);
        $job->execute();

        $mockedCacheService->shouldHaveReceived('clear')->times(1);
        $mockedJobDao->shouldHaveReceived('batchUpdate')->times(1);
        $mockedSettingService->shouldHaveReceived('get')->andReturn(['statistical_dimension' => 'playing']);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
