<?php


namespace Biz\Visualization\Service\Impl;


use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Biz\Visualization\Dao\ActivityStayDailyDao;
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
        foreach ($learnRecords as $userId => $learnRecord){
            $learnRecord = ArrayToolkit::group($learnRecord, 'activityId');
            foreach($learnRecord as $activityId => $activityRecords){
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
}