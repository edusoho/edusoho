<?php

namespace Tests\Unit\Visualization\Job;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityStayDailyDao;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Biz\Visualization\Dao\CoursePlanStayDailyDao;
use Biz\Visualization\Dao\CoursePlanVideoDailyDao;
use Biz\Visualization\Job\RefreshCoursePlanLearnDailyJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class RefreshCoursePlanLearnDailyJobTest extends BaseTestCase
{
    public function testExecuteWithSettingByPlaying()
    {
        $videoDaily = $this->createCoursePlanVideoDaily();
        $stayDaily = $this->createCoursePlanStayDaily();

        $before = $this->createCoursePlanLearnDaily($stayDaily);

        $mockedCacheService = $this->mockBiz('System:CacheService', [
            ['functionName' => 'clear', 'withParams' => ['refresh_course_plan']],
        ]);

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'withParams' => ['videoEffectiveTimeStatistics', []], 'returnValue' => ['statistical_dimension' => 'playing']],
        ]);

        $job = new RefreshCoursePlanLearnDailyJob([], $this->biz);
        $job->execute();
        $after = $this->getCoursePlanLearnDailyDao()->get($before['id']);

        $mockedCacheService->shouldHaveReceived('clear')->times(1);
        $this->assertEquals($stayDaily['sumTime'], $before['sumTime']);
        $this->assertEquals($videoDaily['sumTime'], $after['sumTime']);
        $this->assertNotEquals($before['sumTime'], $after['sumTime']);
    }

    public function testExecuteWithSettingByPage()
    {
        $videoDaily = $this->createCoursePlanVideoDaily();
        $stayDaily = $this->createCoursePlanStayDaily();

        $before = $this->createCoursePlanLearnDaily($videoDaily);

        $mockedCacheService = $this->mockBiz('System:CacheService', [
            ['functionName' => 'clear', 'withParams' => ['refresh_course_plan']],
        ]);

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'withParams' => ['videoEffectiveTimeStatistics', []], 'returnValue' => ['statistical_dimension' => 'page']],
        ]);

        $job = new RefreshCoursePlanLearnDailyJob([], $this->biz);
        $job->execute();
        $after = $this->getCoursePlanLearnDailyDao()->get($before['id']);

        $mockedCacheService->shouldHaveReceived('clear')->times(1);
        $this->assertEquals($videoDaily['sumTime'], $before['sumTime']);
        $this->assertEquals($stayDaily['sumTime'], $after['sumTime']);
        $this->assertNotEquals($before['sumTime'], $after['sumTime']);
    }

    protected function createCoursePlanLearnDaily($data = [])
    {
        return $this->getCoursePlanLearnDailyDao()->create(array_merge([
            'userId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => strtotime(date('Y-m-d 0:0:0')),
            'sumTime' => 100,
            'pureTime' => 60,
        ], $data));
    }

    protected function createCoursePlanStayDaily($data = [])
    {
        return $this->getCoursePlanStayDailyDao()->create(array_merge([
            'userId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => strtotime(date('Y-m-d 0:0:0')),
            'sumTime' => 200,
            'pureTime' => 60,
        ], $data));
    }

    protected function createCoursePlanVideoDaily($data = [])
    {
        return $this->getCoursePlanVideoDailyDao()->create(array_merge([
            'userId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
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
     * @return CoursePlanLearnDailyDao
     */
    protected function getCoursePlanLearnDailyDao()
    {
        return $this->biz->dao('Visualization:CoursePlanLearnDailyDao');
    }

    /**
     * @return CoursePlanStayDailyDao
     */
    protected function getCoursePlanStayDailyDao()
    {
        return $this->biz->dao('Visualization:CoursePlanStayDailyDao');
    }

    /**
     * @return CoursePlanVideoDailyDao
     */
    protected function getCoursePlanVideoDailyDao()
    {
        return $this->biz->dao('Visualization:CoursePlanVideoDailyDao');
    }

    /**
     * @return ActivityStayDailyDao
     */
    protected function getActivityStayDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityStayDailyDao');
    }
}
