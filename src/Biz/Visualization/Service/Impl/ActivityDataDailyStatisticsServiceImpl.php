<?php

namespace Biz\Visualization\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Biz\Visualization\Dao\ActivityStayDailyDao;
use Biz\Visualization\Dao\ActivityVideoDailyDao;
use Biz\Visualization\Dao\ActivityVideoWatchRecordDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;

class ActivityDataDailyStatisticsServiceImpl extends BaseService implements ActivityDataDailyStatisticsService
{
    public function statisticsPageStayDailyData($startTime, $endTime)
    {
        $learnRecords = $this->getActivityLearnRecordDao()->search(
            ['startTime_GE' => $startTime, 'endTime_GE' => $endTime],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'activityId', 'taskId', 'courseId', 'courseSetId', 'startTime', 'endTime', 'duration']
        );
        $learnRecords = ArrayToolkit::group($learnRecords, 'userId');

        $data = [];
        foreach ($learnRecords as $userId => $learnRecord) {
            $learnRecord = ArrayToolkit::group($learnRecord, 'activityId');
            foreach ($learnRecord as $activityId => $activityRecords) {
                $activityRecord = current($activityRecords);
                $data[] = [
                    'userId' => $userId,
                    'activityId' => $activityId,
                    'taskId' => $activityRecord['taskId'],
                    'courseId' => $activityRecord['courseId'],
                    'courseSetId' => $activityRecord['courseSetId'],
                    'dayTime' => $startTime,
                    'sumTime' => array_sum(ArrayToolkit::column($activityRecords, 'duration')),
                    'pureTime' => $this->sumPureTime($activityRecords),
                ];
            }
        }

        return $this->getActivityStayDailyDao()->batchCreate($data);
    }

    public function statisticsVideoDailyData($startTime, $endTime)
    {
        $learnRecords = $this->getActivityVideoWatchRecordDao()->search(
            ['startTime_GE' => $startTime, 'endTime_LT' => $endTime],

            [],
            0,
            PHP_INT_MAX,
            ['userId', 'activityId', 'taskId', 'courseId', 'courseSetId', 'startTime', 'endTime', 'duration']
        );
        $learnRecords = ArrayToolkit::group($learnRecords, 'userId');

        $data = [];
        foreach ($learnRecords as $userId => $userLearnRecords) {
            $userLearnRecords = ArrayToolkit::group($userLearnRecords, 'activityId');
            foreach ($userLearnRecords as $activityId => $activityLearnRecords) {
                $record = current($activityLearnRecords);
                $data[] = [
                    'userId' => $userId,
                    'activityId' => $activityId,
                    'taskId' => $record['taskId'],
                    'courseId' => $record['courseId'],
                    'courseSetId' => $record['courseSetId'],
                    'dayTime' => $startTime,
                    'sumTime' => array_sum(ArrayToolkit::column($activityLearnRecords, 'duration')),
                    'pureTime' => $this->sumPureTime($activityLearnRecords),
                ];
            }
        }

        return $this->getActivityVideoDailyDao()->batchCreate($data);
    }

    public function statisticsLearnDailyData($dayTime)
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $data = [];
        $conditions = ['dayTime' => $dayTime];
        $columns = ['userId', 'activityId', 'taskId', 'courseId', 'courseSetId', 'dayTime', 'sumTime', 'pureTime'];
        if (empty($statisticsSetting) || 'playing' == $statisticsSetting['statistical_dimension']) {
            $data = $this->getActivityVideoDailyDao()->search($conditions, [], 0, PHP_INT_MAX, $columns);
        }

        if ('page' == $statisticsSetting['statistical_dimension']) {
            $data = $this->getActivityStayDailyDao()->search($conditions, [], 0, PHP_INT_MAX, $columns);
        }

        $this->beginTransaction();
        try {
            $this->sumTaskResultPureTime($data);

            $this->getActivityLearnDailyDao()->batchCreate($data);

            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function sumTaskResultPureTime($data)
    {
        $taskResults = $this->getTaskResultService()->searchTaskResults(
            ['userIds' => ArrayToolkit::column($data, 'userId'), 'courseTaskIds' => ArrayToolkit::column($data, 'taskId')],
            [],
            0,
            PHP_INT_MAX,
            ['id', 'pureTime', 'userId', 'courseTaskId']
        );
        $data = ArrayToolkit::groupIndex($data, 'userId', 'taskId');

        $updateFields = [];
        foreach ($taskResults as $taskResult) {
            if (!empty($data[$taskResult['userId']][$taskResult['courseTaskId']])) {
                $learnData = $data[$taskResult['userId']][$taskResult['courseTaskId']];
                $updateFields[] = [
                    'id' => $taskResult['id'],
                    'pureTime' => $taskResult['pureTime'] + $learnData['pureTime'],
                ];
            }
        }

        return $this->getTaskResultService()->batchUpdate(ArrayToolkit::column($updateFields, 'id'), $updateFields);
    }

    public function sumPureTime($records)
    {
        uasort($records, function ($record1, $record2) {
            return $record1['startTime'] > $record2['startTime'];
        });
        $start = 0;
        $end = 0;
        $pureTime = 0;
        foreach ($records as $record) {
            if ($record['startTime'] > $end) {
                $pureTime += $end - $start;
                $start = $record['startTime'];
                $end = $record['endTime'];
            } elseif ($record['endTime'] > $end && $record['startTime'] <= $end) {
                $end = $record['endTime'];
            } else {
                continue;
            }
        }
        $pureTime += $end - $start;

        return $pureTime;
    }

    /**
     * @return ActivityLearnRecordDao
     */
    protected function getActivityLearnRecordDao()
    {
        return $this->createDao('Visualization:ActivityLearnRecordDao');
    }

    /**
     * @return ActivityStayDailyDao
     */
    protected function getActivityStayDailyDao()
    {
        return $this->createDao('Visualization:ActivityStayDailyDao');
    }

    /**
     * @return ActivityVideoWatchRecordDao
     */
    protected function getActivityVideoWatchRecordDao()
    {
        return $this->createDao('Visualization:ActivityVideoWatchRecordDao');
    }

    /**
     * @return ActivityVideoDailyDao
     */
    protected function getActivityVideoDailyDao()
    {
        return $this->createDao('Visualization:ActivityVideoDailyDao');
    }

    /**
     * @return ActivityLearnDailyDao
     */
    protected function getActivityLearnDailyDao()
    {
        return $this->createDao('Visualization:ActivityLearnDailyDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }
}
