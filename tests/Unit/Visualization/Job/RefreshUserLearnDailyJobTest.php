<?php

namespace Tests\Unit\Visualization\Job;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityStayDailyDao;
use Biz\Visualization\Dao\UserLearnDailyDao;
use Biz\Visualization\Dao\UserStayDailyDao;
use Biz\Visualization\Dao\UserVideoDailyDao;
use Biz\Visualization\Job\RefreshUserLearnDailyJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class RefreshUserLearnDailyJobTest extends BaseTestCase
{
    public function testExecuteWithSettingByPlaying()
    {
        $videoDaily = $this->createUserVideoDaily();
        $stayDaily = $this->createUserStayDaily();

        $before = $this->createUserLearnDaily($stayDaily);

        $mockedCacheService = $this->mockBiz('System:CacheService', [
            ['functionName' => 'clear', 'withParams' => ['refresh_user']],
        ]);

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'withParams' => ['videoEffectiveTimeStatistics', []], 'returnValue' => ['statistical_dimension' => 'playing']],
        ]);

        $job = new RefreshUserLearnDailyJob([], $this->biz);
        $job->execute();
        $after = $this->getUserLearnDailyDao()->get($before['id']);

        $mockedCacheService->shouldHaveReceived('clear')->times(1);
        $this->assertEquals($stayDaily['sumTime'], $before['sumTime']);
        $this->assertEquals($videoDaily['sumTime'], $after['sumTime']);
        $this->assertNotEquals($before['sumTime'], $after['sumTime']);
    }

    public function testExecuteWithSettingByPage()
    {
        $videoDaily = $this->createUserVideoDaily();
        $stayDaily = $this->createUserStayDaily();

        $before = $this->createUserLearnDaily($videoDaily);

        $mockedCacheService = $this->mockBiz('System:CacheService', [
            ['functionName' => 'clear', 'withParams' => ['refresh_user']],
        ]);

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'withParams' => ['videoEffectiveTimeStatistics', []], 'returnValue' => ['statistical_dimension' => 'page']],
        ]);

        $job = new RefreshUserLearnDailyJob([], $this->biz);
        $job->execute();
        $after = $this->getUserLearnDailyDao()->get($before['id']);

        $mockedCacheService->shouldHaveReceived('clear')->times(1);
        $this->assertEquals($videoDaily['sumTime'], $before['sumTime']);
        $this->assertEquals($stayDaily['sumTime'], $after['sumTime']);
        $this->assertNotEquals($before['sumTime'], $after['sumTime']);
    }

    protected function createUserLearnDaily($data = [])
    {
        return $this->getUserLearnDailyDao()->create(array_merge([
            'userId' => 1,
            'dayTime' => strtotime(date('Y-m-d 0:0:0')),
            'sumTime' => 100,
            'pureTime' => 60,
        ], $data));
    }

    protected function createUserStayDaily($data = [])
    {
        return $this->getUserStayDailyDao()->create(array_merge([
            'userId' => 1,
            'dayTime' => strtotime(date('Y-m-d 0:0:0')),
            'sumTime' => 200,
            'pureTime' => 60,
        ], $data));
    }

    protected function createUserVideoDaily($data = [])
    {
        return $this->getUserVideoDailyDao()->create(array_merge([
            'userId' => 1,
            'dayTime' => strtotime(date('Y-m-d 0:0:0')),
            'sumTime' => 60,
            'pureTime' => 30,
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
     * @return UserLearnDailyDao
     */
    protected function getUserLearnDailyDao()
    {
        return $this->biz->dao('Visualization:UserLearnDailyDao');
    }

    /**
     * @return UserStayDailyDao
     */
    protected function getUserStayDailyDao()
    {
        return $this->biz->dao('Visualization:UserStayDailyDao');
    }

    /**
     * @return UserVideoDailyDao
     */
    protected function getUserVideoDailyDao()
    {
        return $this->biz->dao('Visualization:UserVideoDailyDao');
    }

    /**
     * @return ActivityStayDailyDao
     */
    protected function getActivityStayDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityStayDailyDao');
    }
}
