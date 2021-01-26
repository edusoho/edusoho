<?php

namespace Tests\Unit\Visualization\Job;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Dao\ActivityStayDailyDao;
use Biz\Visualization\Dao\ActivityVideoDailyDao;
use Biz\Visualization\Job\RefreshActivityLearnDailyJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class RefreshActivityLearnDailyJobTest extends BaseTestCase
{
    public function testExecuteWithSettingByPlaying()
    {
        $videoDaily = $this->createActivityVideoDaily();
        $stayDaily = $this->createActivityStayDaily();

        $before = $this->createActivityLearnDaily($stayDaily);

        $mockedCacheService = $this->mockBiz('System:CacheService', [
            ['functionName' => 'clear', 'withParams' => ['refresh_activity']],
        ]);

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'withParams' => ['videoEffectiveTimeStatistics', []], 'returnValue' => ['statistical_dimension' => 'playing']],
        ]);

        $job = new RefreshActivityLearnDailyJob([], $this->biz);
        $job->execute();
        $after = $this->getActivityLearnDailyDao()->get($before['id']);

        $mockedCacheService->shouldHaveReceived('clear')->times(1);
        $this->assertEquals($stayDaily['sumTime'], $before['sumTime']);
        $this->assertEquals($videoDaily['sumTime'], $after['sumTime']);
        $this->assertNotEquals($before['sumTime'], $after['sumTime']);
    }

    public function testExecuteWithSettingByPage()
    {
        $videoDaily = $this->createActivityVideoDaily();
        $stayDaily = $this->createActivityStayDaily();

        $before = $this->createActivityLearnDaily($videoDaily);

        $mockedCacheService = $this->mockBiz('System:CacheService', [
            ['functionName' => 'clear', 'withParams' => ['refresh_activity']],
        ]);

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'withParams' => ['videoEffectiveTimeStatistics', []], 'returnValue' => ['statistical_dimension' => 'page']],
        ]);

        $job = new RefreshActivityLearnDailyJob([], $this->biz);
        $job->execute();
        $after = $this->getActivityLearnDailyDao()->get($before['id']);

        $mockedCacheService->shouldHaveReceived('clear')->times(1);
        $this->assertEquals($videoDaily['sumTime'], $before['sumTime']);
        $this->assertEquals($stayDaily['sumTime'], $after['sumTime']);
        $this->assertNotEquals($before['sumTime'], $after['sumTime']);
    }

    protected function createActivityLearnDaily($data = [])
    {
        return $this->getActivityLearnDailyDao()->create(array_merge([
            'userId' => 1,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'mediaType' => 'video',
            'dayTime' => strtotime(date('Y-m-d 0:0:0')),
            'sumTime' => 120,
            'pureTime' => 60,
        ], $data));
    }

    protected function createActivityStayDaily($data = [])
    {
        return $this->getActivityStayDailyDao()->create(array_merge([
            'userId' => 1,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'mediaType' => 'video',
            'dayTime' => strtotime(date('Y-m-d 0:0:0')),
            'sumTime' => 120,
            'pureTime' => 60,
        ], $data));
    }

    protected function createActivityVideoDaily($data = [])
    {
        return $this->getActivityVideoDailyDao()->create(array_merge([
            'userId' => 1,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => strtotime(date('Y-m-d 0:0:0')),
            'sumTime' => 60,
            'pureTime' => 30,
        ], $data));
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return ActivityVideoDailyDao
     */
    protected function getActivityVideoDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityVideoDailyDao');
    }

    /**
     * @return ActivityLearnDailyDao
     */
    protected function getActivityLearnDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityLearnDailyDao');
    }

    /**
     * @return ActivityStayDailyDao
     */
    protected function getActivityStayDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityStayDailyDao');
    }
}
