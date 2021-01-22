<?php

namespace Biz\Visualization\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshActivityLearnDailyJob extends AbstractJob
{
    const TYPE = 'activity';

    const LIMIT = 10000;

    protected $setting = '';

    public function execute()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $this->setting = $statisticsSetting['statistical_dimension'];
        $this->refreshActivityLearnDaily();
        $this->refreshCourseTaskResult();

        $jobSetting = $this->getSettingService()->get('refreshLearnDailyJob', []);
        unset($jobSetting[self::TYPE]);
        empty($jobSetting) ? $this->getSettingService()->delete('refreshLearnDailyJob') : $this->getSettingService()->set('refreshLearnDailyJob', $jobSetting);
    }

    protected function refreshActivityLearnDaily()
    {
        $table = 'page' == $this->setting ? 'activity_stay_daily' : 'activity_video_daily';
        $count = $this->getActivityLearnDailyDao()->count(['mediaType' => 'video']);
        $limit = self::LIMIT;
        $totalPage = $count / $limit;
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $sql = "
                SELECT  ald.id AS id,  
                    IF(t.sumTime, t.sumTime, 0) AS sumTime, 
                    IF(t.pureTime, t.pureTime, 0) AS pureTime
                FROM activity_learn_daily ald
                LEFT JOIN {$table} t 
                ON ald.activityId  =  t.activityId  AND  ald.userId  =  t.userId  AND  ald.dayTime = t.dayTime 
                WHERE ald.mediaType = 'video' LIMIT {$start}, {$limit};
            ";
            $data = $this->biz['db']->fetchAll($sql);
            if (!empty($data)) {
                $this->getActivityLearnDailyDao()->batchUpdate(array_column($data, 'id'), $data);
            }
        }
    }

    protected function refreshCourseTaskResult()
    {
        $count = $this->biz['db']->fetchColumn("SELECT COUNT(*) FROM course_task_result ctr LEFT JOIN activity a ON ctr.activityId = a.id WHERE a.mediaType = 'video';");
        $limit = 1000;
        $totalPage = $count / $limit;
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $updateData = [];
            $sql = "SELECT ctr.id AS id, ctr.activityId AS activityId, ctr.userId AS userId, IF (ctr.stayTime, ctr.stayTime, 0) AS stayTime FROM course_task_result ctr LEFT JOIN activity a ON ctr.activityId = a.id WHERE a.mediaType = 'video' LIMIT {$start}, {$limit};";
            $data = $this->biz['db']->fetchAll($sql);
            if ('page' == $this->setting) {
                foreach ($data as $result) {
                    $updateData[] = ['id' => $result['id'], 'sumTime' => $result['stayTime']];
                }
            } else {
                $activityIds = array_column($data, 'activityId');
                $userIds = array_column($data, 'userId');
                $activityMarks = str_repeat('?,', count($activityIds) - 1).'?';
                $userMarks = str_repeat('?,', count($userIds) - 1).'?';
                $sql = "SELECT id, activityId, userId, sumTime FROM activity_video_daily WHERE activityId IN ({$activityMarks}) AND userId IN ({$userMarks})";
                $records = $this->biz['db']->fetchAll($sql, array_merge($activityIds, $userIds));
                $records = ArrayToolkit::group($records, 'userId');
                foreach ($data as $result) {
                    if (empty($records[$result['userId']])) {
                        continue;
                    }

                    $userRecords = ArrayToolkit::group($records[$result['userId']], 'activityId');
                    if (empty($userRecords[$result['activityId']])) {
                        continue;
                    }

                    $sumTime = array_sum(array_column($userRecords[$result['activityId']], 'sumTime'));
                    $updateData[] = ['id' => $result['id'], 'sumTime' => $sumTime ? $sumTime : 0];
                }
            }

            if (!empty($updateData)) {
                $this->getActivityLearnDailyDao()->batchUpdate(array_column($updateData, 'id'), $updateData);
            }
        }
    }

    /**
     * @return ActivityDataDailyStatisticsService
     */
    protected function getActivityDataDailyStatisticsService()
    {
        return $this->biz->service('Visualization:ActivityDataDailyStatisticsService');
    }

    /**
     * @return ActivityLearnDailyDao
     */
    protected function getActivityLearnDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityLearnDailyDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }
}
