<?php

namespace Tests\Unit\Visualization\Service;

use Biz\BaseTestCase;
use Biz\Task\Dao\TaskResultDao;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Dao\ActivityStayDailyDao;
use Biz\Visualization\Dao\ActivityVideoDailyDao;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Biz\Visualization\Dao\CoursePlanStayDailyDao;
use Biz\Visualization\Dao\CoursePlanVideoDailyDao;
use Biz\Visualization\Dao\UserLearnDailyDao;
use Biz\Visualization\Dao\UserStayDailyDao;
use Biz\Visualization\Dao\UserVideoDailyDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;

class ActivityDataDailyStatisticsServiceTest extends BaseTestCase
{
    public function testStatisticsPageStayDailyData()
    {
        $this->mockBiz('Visualization:ActivityLearnRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120, 'mediaType' => 'text'],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120, 'mediaType' => 'text'],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793870, 'duration' => 120, 'mediaType' => 'text'],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793810, 'duration' => 60, 'mediaType' => 'text'],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsPageStayDailyData(1604764800, 1604851199);
        $result = $this->getActivityStayDailyDao()->search([], [], 0, 1);
        $this->assertEquals(420, $result[0]['sumTime']);
        $this->assertEquals(260, $result[0]['pureTime']);
    }

    public function testStatisticsVideoDailyData()
    {
        $this->mockBiz('Visualization:ActivityVideoWatchRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793870, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793810, 'duration' => 60],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsVideoDailyData(1604764800, 1604851199);
        $result = $this->getActivityVideoDailyDao()->search([], [], 0, 1);
        $this->assertEquals(420, $result[0]['sumTime']);
        $this->assertEquals(260, $result[0]['pureTime']);
    }

    public function testStatisticsLearnDailyData()
    {
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['statistical_dimension' => 'playing'], 'runTimes' => 1],
            ['functionName' => 'get', 'returnValue' => ['statistical_dimension' => 'page'], 'runTimes' => 1],
        ]);

        $this->mockBiz('Visualization:ActivityVideoDailyDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 340, 'pureTime' => 120],
            ]],
        ]);

        $this->mockBiz('Visualization:ActivityStayDailyDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 440, 'pureTime' => 220, 'mediaType' => 'video'],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 540, 'pureTime' => 320, 'mediaType' => 'text'],
            ], 'runTimes' => 1],
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 440, 'pureTime' => 220, 'mediaType' => 'video'],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 540, 'pureTime' => 320, 'mediaType' => 'text'],
            ], 'runTimes' => 1],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsLearnDailyData(1604793600);

        $result = $this->getActivityLearnDailyDao()->search([], [], 0, 2);
        $this->assertEquals(540, $result[0]['sumTime']);
        $this->assertEquals(340, $result[1]['sumTime']);

        $this->getActivityDataDailyStatisticsService()->statisticsLearnDailyData(1604793600);
        $result = $this->getActivityLearnDailyDao()->search([], [], 0, 2);
        $this->assertEquals(440, $result[0]['sumTime']);
        $this->assertEquals(540, $result[1]['sumTime']);
    }

    public function testStatisticsUserLearnDailyData()
    {
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['statistical_dimension' => 'playing'], 'runTimes' => 1],
            ['functionName' => 'get', 'returnValue' => ['statistical_dimension' => 'page'], 'runTimes' => 1],
        ]);

        $this->mockBiz('Visualization:ActivityVideoWatchRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793660, 'duration' => 60],
            ]],
        ]);

        $this->mockBiz('Visualization:ActivityLearnRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120, 'mediaType' => 'video'],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120, 'mediaType' => 'text'],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120, 'mediaType' => 'text'],
            ]],
        ]);

        $this->mockBiz('Visualization:UserStayDailyDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'dayTime' => 1604793600, 'sumTime' => 440, 'pureTime' => 220],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsUserLearnDailyData(1604793600);
        $result = $this->getUserLearnDailyDao()->search([], [], 0, 2);
        $this->assertEquals(300, $result[0]['sumTime']);
        $this->assertEquals(180, $result[0]['pureTime']);

        $this->getActivityDataDailyStatisticsService()->statisticsUserLearnDailyData(1604793600);
        $result = $this->getUserLearnDailyDao()->search([], [], 0, 2);
        $this->assertEquals(440, $result[0]['sumTime']);
        $this->assertEquals(220, $result[0]['pureTime']);
    }

    public function testStatisticsCoursePlanLearnDailyData()
    {
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['statistical_dimension' => 'playing'], 'runTimes' => 1],
            ['functionName' => 'get', 'returnValue' => ['statistical_dimension' => 'page'], 'runTimes' => 1],
        ]);

        $this->mockBiz('Visualization:ActivityVideoWatchRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793660, 'duration' => 60],
            ]],
        ]);

        $this->mockBiz('Visualization:ActivityLearnRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120, 'mediaType' => 'video'],
                ['userId' => 1, 'activityId' => 3, 'taskId' => 3, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793640, 'endTime' => 1604793760, 'duration' => 120, 'mediaType' => 'text'],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 2, 'courseSetId' => 2, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120, 'mediaType' => 'text'],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 2, 'courseSetId' => 2, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120, 'mediaType' => 'text'],
            ]],
        ]);

        $this->mockBiz('Visualization:CoursePlanStayDailyDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 440, 'pureTime' => 220],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanLearnDailyData(1604793600);
        $result = $this->getCoursePlanLearnDailyDao()->search([], [], 0, 2);
        $this->assertEquals(180, $result[0]['sumTime']);
        $this->assertEquals(160, $result[0]['pureTime']);
        $this->assertEquals(240, $result[1]['sumTime']);
        $this->assertEquals(120, $result[1]['pureTime']);

        $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanLearnDailyData(1604793600);
        $result = $this->getCoursePlanLearnDailyDao()->search([], [], 0, 2);
        $this->assertEquals(440, $result[0]['sumTime']);
        $this->assertEquals(220, $result[0]['pureTime']);
    }

    public function testStatisticsCoursePlanStayDailyData()
    {
        $this->mockBiz('Visualization:ActivityLearnRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793870, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793810, 'duration' => 60],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanStayDailyData(1604764800, 1604851199);
        $result = $this->getCoursePlanStayDailyDao()->search([], [], 0, 1);
        $this->assertEquals(420, $result[0]['sumTime']);
        $this->assertEquals(260, $result[0]['pureTime']);
    }

    public function testStatisticsCoursePlanVideoDailyData()
    {
        $this->mockBiz('Visualization:ActivityVideoWatchRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793870, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793810, 'duration' => 60],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanVideoDailyData(1604764800, 1604851199);
        $result = $this->getCoursePlanVideoDailyDao()->search([], [], 0, 1);
        $this->assertEquals(420, $result[0]['sumTime']);
        $this->assertEquals(260, $result[0]['pureTime']);
    }

    public function testStatisticsUserStayDailyData()
    {
        $this->mockBiz('Visualization:ActivityLearnRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793870, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793810, 'duration' => 60],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsUserStayDailyData(1604764800, 1604851199);
        $result = $this->getUserStayDailyDao()->search([], [], 0, 1);
        $this->assertEquals(420, $result[0]['sumTime']);
        $this->assertEquals(260, $result[0]['pureTime']);
    }

    public function testStatisticsUserVideoDailyData()
    {
        $this->mockBiz('Visualization:ActivityVideoWatchRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793870, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793810, 'duration' => 60],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsUserVideoDailyData(1604764800, 1604851199);
        $result = $this->getUserVideoDailyDao()->search([], [], 0, 1);
        $this->assertEquals(420, $result[0]['sumTime']);
        $this->assertEquals(260, $result[0]['pureTime']);
    }

    public function testSumTaskResultTime()
    {
        $this->mockBiz('Visualization:ActivityStayDailyDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 140, 'pureTime' => 120],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 240, 'pureTime' => 120],
            ]],
        ]);

        $this->mockBiz('Visualization:ActivityLearnDailyDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 140, 'pureTime' => 120],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 240, 'pureTime' => 120],
            ]],
        ]);

        $this->mockBiz('Visualization:ActivityVideoDailyDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 340, 'pureTime' => 240],
                ['userId' => 1, 'activityId' => 2, 'taskId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'dayTime' => 1604793600, 'sumTime' => 240, 'pureTime' => 120],
            ]],
        ]);

        $this->mockTaskResult([
            'userId' => 1,
            'courseTaskId' => 1,
            'status' => 'finish',
        ]);

        $this->getActivityDataDailyStatisticsService()->sumTaskResultTime(1604793600);
        $result = $this->getTaskResultDao()->search([], [], 0, 1);
        $this->assertEquals(140, $result[0]['sumTime']);
        $this->assertEquals(120, $result[0]['pureTime']);
        $this->assertEquals(240, $result[0]['pureWatchTime']);
    }

    protected function mockTaskResult($fields = [])
    {
        $taskResult = array_merge([
            'activityId' => 1,
            'courseTaskId' => 2,
            'time' => 1,
            'watchTime' => 1,
            'userId' => 1,
            'courseId' => 1,
            'pureTime' => 0,
        ], $fields);

        return $this->getTaskResultDao()->create($taskResult);
    }

    public function testGetVideoEffectiveTimeStatisticsSetting()
    {
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['statistical_dimension' => 'page', 'play_rule' => 'no_action']],
        ]);

        $result = $this->getActivityDataDailyStatisticsService()->getVideoEffectiveTimeStatisticsSetting();
        $this->assertEquals('page', $result['statistical_dimension']);
        $this->assertEquals('no_action', $result['play_rule']);
    }

    public function testGetDailyLearnData()
    {
        $this->batchMockUserLearnDailyData();

        $result = $this->getActivityDataDailyStatisticsService()->getDailyLearnData(1, '', '');
        $this->assertEquals(440, $result[0]['learnedTime']);
        $this->assertEquals(540, $result[1]['learnedTime']);
    }

    protected function batchMockUserLearnDailyData()
    {
        return $this->getUserLearnDailyDao()->batchCreate(
            [
                ['userId' => 1, 'dayTime' => 1604793600, 'sumTime' => 440, 'pureTime' => 220],
                ['userId' => 1, 'dayTime' => 1604880000, 'sumTime' => 540, 'pureTime' => 320],
                ['userId' => 2, 'dayTime' => 1604793600, 'sumTime' => 540, 'pureTime' => 320],
            ]
        );
    }

    /**
     * @return UserLearnDailyDao
     */
    protected function getUserLearnDailyDao()
    {
        return $this->biz->dao('Visualization:UserLearnDailyDao');
    }

    /**
     * @return CoursePlanVideoDailyDao
     */
    protected function getCoursePlanVideoDailyDao()
    {
        return $this->biz->dao('Visualization:CoursePlanVideoDailyDao');
    }

    /**
     * @return CoursePlanStayDailyDao
     */
    protected function getCoursePlanStayDailyDao()
    {
        return $this->biz->dao('Visualization:CoursePlanStayDailyDao');
    }

    /**
     * @return TaskResultDao
     */
    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }

    /**
     * @return ActivityDataDailyStatisticsService
     */
    protected function getActivityDataDailyStatisticsService()
    {
        return $this->biz->service('Visualization:ActivityDataDailyStatisticsService');
    }

    /**
     * @return ActivityStayDailyDao
     */
    protected function getActivityStayDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityStayDailyDao');
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
     * @return UserStayDailyDao
     */
    protected function getUserStayDailyDao()
    {
        return $this->createDao('Visualization:UserStayDailyDao');
    }

    /**
     * @return UserVideoDailyDao
     */
    protected function getUserVideoDailyDao()
    {
        return $this->createDao('Visualization:UserVideoDailyDao');
    }

    /**
     * @return CoursePlanLearnDailyDao
     */
    protected function getCoursePlanLearnDailyDao()
    {
        return $this->createDao('Visualization:CoursePlanLearnDailyDao');
    }
}
