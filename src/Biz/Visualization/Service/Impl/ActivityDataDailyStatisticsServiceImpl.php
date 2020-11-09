<?php

namespace Biz\Visualization\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Visualization\Dao\ActivityVideoDailyDao;
use Biz\Visualization\Dao\ActivityVideoWatchRecordDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;

class ActivityDataDailyStatisticsServiceImpl extends BaseService implements ActivityDataDailyStatisticsService
{
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
                $pureTime = $pureTime + ($end - $start);
                $start = $record['startTime'];
                $end = $record['endTime'];
            } elseif ($record['endTime'] > $end && $record['startTime'] <= $end) {
                $end = $record['endTime'];
            } else {
                continue;
            }
        }
        $pureTime = $pureTime + ($end - $start);

        return $pureTime;
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
}