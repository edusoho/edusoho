<?php

namespace Biz\Visualization\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Biz\Visualization\Dao\ActivityStayDailyDao;
use Biz\Visualization\Dao\ActivityVideoDailyDao;
use Biz\Visualization\Dao\ActivityVideoWatchRecordDao;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Biz\Visualization\Dao\CoursePlanStayDailyDao;
use Biz\Visualization\Dao\CoursePlanVideoDailyDao;
use Biz\Visualization\Dao\UserLearnDailyDao;
use Biz\Visualization\Dao\UserStayDailyDao;
use Biz\Visualization\Dao\UserVideoDailyDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;

class ActivityDataDailyStatisticsServiceImpl extends BaseService implements ActivityDataDailyStatisticsService
{
    public function statisticsPageStayDailyData($startTime, $endTime)
    {
        $learnRecords = $this->getActivityLearnRecordDao()->search(
            ['startTime_GE' => $startTime, 'endTime_LT' => $endTime],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'activityId', 'taskId', 'courseId', 'courseSetId', 'startTime', 'endTime', 'duration', 'mediaType']
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
                    'mediaType' => $activityRecord['mediaType'],
                    'dayTime' => $startTime,
                    'sumTime' => array_sum(ArrayToolkit::column($activityRecords, 'duration')),
                    'pureTime' => $this->sumPureTime($activityRecords),
                ];
            }
        }

        $this->getActivityStayDailyDao()->batchDelete(['dayTime' => $startTime]);

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

        $this->getActivityVideoDailyDao()->batchDelete(['dayTime' => $startTime]);

        return $this->getActivityVideoDailyDao()->batchCreate($data);
    }

    public function statisticsLearnDailyData($dayTime)
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $conditions = ['dayTime' => $dayTime];
        $columns = ['userId', 'activityId', 'taskId', 'courseId', 'courseSetId', 'dayTime', 'sumTime', 'pureTime'];
        if (empty($statisticsSetting) || 'playing' === $statisticsSetting['statistical_dimension']) {
            $stayData = $this->getActivityStayDailyDao()->search(
                $conditions,
                [],
                0,
                PHP_INT_MAX,
                array_merge($columns, ['mediaType'])
            );
            $videoData = $this->getActivityVideoDailyDao()->search($conditions, [], 0, PHP_INT_MAX, $columns);
            $data = [];

            foreach (array_merge($stayData, $videoData) as $dailyData) {
                if (isset($dailyData['mediaType']) && 'video' !== $dailyData['mediaType']) {
                    $data[] = $dailyData;
                }

                if (!isset($dailyData['mediaType'])) {
                    $data[] = array_merge($dailyData, ['mediaType' => 'video']);
                }
            }
        } else {
            $data = $this->getActivityStayDailyDao()->search(
                $conditions,
                [],
                0,
                PHP_INT_MAX,
                array_merge($columns, ['mediaType'])
            );
        }

        $this->getActivityLearnDailyDao()->batchDelete($conditions);

        return $this->getActivityLearnDailyDao()->batchCreate($data);
    }

    public function statisticsUserLearnDailyData($dayTime)
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $conditions = ['dayTime' => $dayTime];
        $columns = ['userId', 'dayTime', 'sumTime', 'pureTime'];
        if (empty($statisticsSetting) || 'playing' == $statisticsSetting['statistical_dimension']) {
            $totalRecords = $this->findMixedRecords($dayTime);
            $data = [];
            foreach ($totalRecords as $userId => $records) {
                $data[] = [
                    'userId' => $userId,
                    'dayTime' => $dayTime,
                    'sumTime' => array_sum(ArrayToolkit::column($records, 'duration')),
                    'pureTime' => $this->sumPureTime($records),
                ];
            }
        } else {
            $data = $this->getUserStayDailyDao()->search($conditions, [], 0, PHP_INT_MAX, $columns);
        }

        $this->getUserLearnDailyDao()->batchDelete($conditions);

        return $this->getUserLearnDailyDao()->batchCreate($data);
    }

    public function statisticsCoursePlanLearnDailyData($dayTime)
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $conditions = ['dayTime' => $dayTime];
        $columns = ['userId', 'courseId', 'courseSetId', 'dayTime', 'sumTime', 'pureTime'];
        if (empty($statisticsSetting) || 'playing' == $statisticsSetting['statistical_dimension']) {
            $totalRecords = $this->findMixedRecords($dayTime);
            $data = [];
            foreach ($totalRecords as $userId => $userRecords) {
                $userRecords = ArrayToolkit::group($userRecords, 'courseId');
                foreach ($userRecords as $courseId => $courseRecords) {
                    $record = current($courseRecords);
                    $data[] = [
                        'userId' => $userId,
                        'courseId' => $courseId,
                        'courseSetId' => $record['courseSetId'],
                        'dayTime' => $dayTime,
                        'sumTime' => array_sum(ArrayToolkit::column($courseRecords, 'duration')),
                        'pureTime' => $this->sumPureTime($courseRecords),
                    ];
                }
            }
        } else {
            $data = $this->getCoursePlanStayDailyDao()->search($conditions, [], 0, PHP_INT_MAX, $columns);
        }

        $this->getCoursePlanLearnDailyDao()->batchDelete($conditions);

        return $this->getCoursePlanLearnDailyDao()->batchCreate($data);
    }

    protected function findMixedRecords($dayTime)
    {
        $watchRecords = $this->getActivityVideoWatchRecordDao()->search(
            ['startTime_GE' => $dayTime, 'endTime_LT' => $dayTime + 86400],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'activityId', 'taskId', 'courseId', 'courseSetId', 'startTime', 'endTime', 'duration']
        );
        $learnRecords = $this->getActivityLearnRecordDao()->search(
            ['startTime_GE' => $dayTime, 'endTime_LT' => $dayTime + 86400],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'activityId', 'taskId', 'courseId', 'courseSetId', 'startTime', 'endTime', 'duration', 'mediaType']
        );
        foreach ($learnRecords as $key => $record) {
            if ('video' == $record['mediaType']) {
                unset($learnRecords[$key]);
            }
        }

        $totalRecords = array_merge($learnRecords, $watchRecords);

        return ArrayToolkit::group($totalRecords, 'userId');
    }

    public function statisticsCoursePlanStayDailyData($startTime, $endTime)
    {
        $learnRecords = $this->getActivityLearnRecordDao()->search(
            ['startTime_GE' => $startTime, 'endTime_LT' => $endTime],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'courseId', 'courseSetId', 'startTime', 'endTime', 'duration']
        );
        $learnRecords = ArrayToolkit::group($learnRecords, 'userId');

        $data = [];
        foreach ($learnRecords as $userId => $learnRecord) {
            $learnRecord = ArrayToolkit::group($learnRecord, 'courseId');
            foreach ($learnRecord as $courseId => $courseRecords) {
                $courseRecord = current($courseRecords);
                $data[] = [
                    'userId' => $userId,
                    'courseId' => $courseId,
                    'courseSetId' => $courseRecord['courseSetId'],
                    'dayTime' => $startTime,
                    'sumTime' => array_sum(ArrayToolkit::column($courseRecords, 'duration')),
                    'pureTime' => $this->sumPureTime($courseRecords),
                ];
            }
        }

        $this->getCoursePlanStayDailyDao()->batchDelete(['dayTime' => $startTime]);

        return $this->getCoursePlanStayDailyDao()->batchCreate($data);
    }

    public function statisticsCoursePlanVideoDailyData($startTime, $endTime)
    {
        $learnRecords = $this->getActivityVideoWatchRecordDao()->search(
            ['startTime_GE' => $startTime, 'endTime_LT' => $endTime],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'courseId', 'courseSetId', 'startTime', 'endTime', 'duration']
        );
        $learnRecords = ArrayToolkit::group($learnRecords, 'userId');

        $data = [];
        foreach ($learnRecords as $userId => $userLearnRecords) {
            $userLearnRecords = ArrayToolkit::group($userLearnRecords, 'courseId');
            foreach ($userLearnRecords as $courseId => $courseLearnRecords) {
                $record = current($courseLearnRecords);
                $data[] = [
                    'userId' => $userId,
                    'courseId' => $courseId,
                    'courseSetId' => $record['courseSetId'],
                    'dayTime' => $startTime,
                    'sumTime' => array_sum(ArrayToolkit::column($courseLearnRecords, 'duration')),
                    'pureTime' => $this->sumPureTime($courseLearnRecords),
                ];
            }
        }

        $this->getCoursePlanVideoDailyDao()->batchDelete(['dayTime' => $startTime]);

        return $this->getCoursePlanVideoDailyDao()->batchCreate($data);
    }

    public function statisticsUserStayDailyData($startTime, $endTime)
    {
        $learnRecords = $this->getActivityLearnRecordDao()->search(
            ['startTime_GE' => $startTime, 'endTime_LT' => $endTime],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'startTime', 'endTime', 'duration']
        );
        $learnRecords = ArrayToolkit::group($learnRecords, 'userId');

        $data = [];
        foreach ($learnRecords as $userId => $learnRecord) {
            $data[] = [
                'userId' => $userId,
                'dayTime' => $startTime,
                'sumTime' => array_sum(ArrayToolkit::column($learnRecord, 'duration')),
                'pureTime' => $this->sumPureTime($learnRecord),
            ];
        }

        $this->getUserStayDailyDao()->batchDelete(['dayTime' => $startTime]);

        return $this->getUserStayDailyDao()->batchCreate($data);
    }

    public function statisticsUserVideoDailyData($startTime, $endTime)
    {
        $watchRecords = $this->getActivityVideoWatchRecordDao()->search(
            ['startTime_GE' => $startTime, 'endTime_LT' => $endTime],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'startTime', 'endTime', 'duration']
        );
        $watchRecords = ArrayToolkit::group($watchRecords, 'userId');

        $data = [];
        foreach ($watchRecords as $userId => $userWatchRecords) {
            $data[] = [
                'userId' => $userId,
                'dayTime' => $startTime,
                'sumTime' => array_sum(ArrayToolkit::column($userWatchRecords, 'duration')),
                'pureTime' => $this->sumPureTime($userWatchRecords),
            ];
        }

        $this->getUserVideoDailyDao()->batchDelete(['dayTime' => $startTime]);

        return $this->getUserVideoDailyDao()->batchCreate($data);
    }

    public function sumTaskResultTime($dayTime)
    {
        $activityRecords = $this->getActivityLearnDailyDao()->search(
            ['dayTime' => $dayTime],
            [],
            0,
            PHP_INT_MAX,
            ['userId', 'activityId', 'taskId']
        );
        $taskResults = $this->getTaskResultService()->searchTaskResults(
            ['userIds' => ArrayToolkit::column($activityRecords, 'userId'), 'courseTaskIds' => ArrayToolkit::column($activityRecords, 'taskId')],
            [],
            0,
            PHP_INT_MAX,
            ['id', 'userId', 'courseTaskId']
        );
        $taskResults = ArrayToolkit::groupIndex($taskResults, 'userId', 'courseTaskId');
        $activityRecords = ArrayToolkit::group($activityRecords, 'userId');
        $updateFields = [];
        foreach ($activityRecords as $userId => $userRecords) {
            $stayRecords = $this->getActivityStayDailyDao()->search(
                ['userId' => $userId, 'taskIds' => ArrayToolkit::column($userRecords, 'taskId')],
                [],
                0,
                PHP_INT_MAX,
                ['userId', 'taskId', 'pureTime', 'sumTime']
            );
            $stayRecords = ArrayToolkit::group($stayRecords, 'taskId');
            $learnRecords = $this->getActivityLearnDailyDao()->search(
                ['userId' => $userId, 'taskIds' => ArrayToolkit::column($userRecords, 'taskId')],
                [],
                0,
                PHP_INT_MAX,
                ['userId', 'taskId', 'pureTime', 'sumTime']
            );
            $learnRecords = ArrayToolkit::group($learnRecords, 'taskId');
            $watchRecords = $this->getActivityVideoDailyDao()->search(
                ['userId' => $userId, 'taskIds' => ArrayToolkit::column($userRecords, 'taskId')],
                [],
                0,
                PHP_INT_MAX,
                ['userId', 'taskId', 'pureTime', 'sumTime']
            );
            $watchRecords = ArrayToolkit::group($watchRecords, 'taskId');
            foreach ($userRecords as $record) {
                if (!empty($taskResults[$userId][$record['taskId']])) {
                    $taskResult = $taskResults[$userId][$record['taskId']];
                    $updateFields[] = [
                        'id' => $taskResult['id'],
                        'sumTime' => array_sum(ArrayToolkit::column($learnRecords[$record['taskId']], 'sumTime')),
                        'stayTime' => array_sum(ArrayToolkit::column($stayRecords[$record['taskId']], 'sumTime')),
//                        'watchTime' => empty($watchRecords[$record['taskId']]) ? 0 : array_sum(ArrayToolkit::column($watchRecords[$record['taskId']], 'sumTime')),
                        'pureTime' => array_sum(ArrayToolkit::column($learnRecords[$record['taskId']], 'pureTime')),
                        'pureStayTime' => array_sum(ArrayToolkit::column($stayRecords[$record['taskId']], 'pureTime')),
                        'pureWatchTime' => empty($watchRecords[$record['taskId']]) ? 0 : array_sum(ArrayToolkit::column($watchRecords[$record['taskId']], 'pureTime')),
                    ];
                }
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

    public function findUserLearnRecords($conditions)
    {
        $records = $this->getUserLearnDailyDao()->sumUserLearnTime($this->analysisCondition($conditions));

        return ArrayToolkit::index($records, 'userId');
    }

    public function getVideoEffectiveTimeStatisticsSetting()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);

        if (empty($statisticsSetting)) {
            $statisticsSetting = [
                'statistical_dimension' => 'page',
                'play_rule' => 'no_action',
            ];
            $this->getSettingService()->set('videoEffectiveTimeStatistics', $statisticsSetting);
        }

        return $statisticsSetting;
    }

    private function analysisCondition($conditions)
    {
        $conditions = ArrayToolkit::parts($conditions, ['startDate', 'endDate', 'userIds']);
        if (!empty($conditions['startDate']) || !empty($conditions['endDate'])) {
            $conditions['dayTime_GE'] = !empty($conditions['startDate']) ? strtotime($conditions['startDate']) : '';
            $conditions['dayTime_LE'] = !empty($conditions['endDate']) ? strtotime($conditions['endDate']) : '';
            unset($conditions['startDate']);
            unset($conditions['endDate']);
        }

        return $conditions;
    }

    public function getDailyLearnData($userId, $startTime, $endTime)
    {
        return $this->getUserLearnDailyDao()->findUserDailyLearnTimeByDate(['userId' => $userId, 'dayTime_GE' => $startTime, 'dayTime_LT' => $endTime]);
    }

    /**
     * @return UserLearnDailyDao
     */
    protected function getUserLearnDailyDao()
    {
        return $this->createDao('Visualization:UserLearnDailyDao');
    }

    /**
     * @return CoursePlanVideoDailyDao
     */
    protected function getCoursePlanVideoDailyDao()
    {
        return $this->createDao('Visualization:CoursePlanVideoDailyDao');
    }

    /**
     * @return CoursePlanStayDailyDao
     */
    protected function getCoursePlanStayDailyDao()
    {
        return $this->createDao('Visualization:CoursePlanStayDailyDao');
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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
