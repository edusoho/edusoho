<?php

namespace Biz\Visualization\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshLearnDailyJob extends AbstractJob
{
    protected $setting = '';

    public function execute()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $this->setting = $statisticsSetting['statistical_dimension'];
        $this->refreshActivityLearnDaily();
        $this->refreshCourseTaskResult();
    }

    protected function refreshActivityLearnDaily()
    {
        $table = 'page' == $this->setting ? 'activity_stay_daily' : 'activity_video_daily';
        $count = $this->getActivityLearnDailyDao()->count(['mediaType' => 'video']);
        $limit = 10000;
        $totalPage = $count / $limit;
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $sql = "select  
                      ald.id as id,  
                      if(t.sumTime is null, 0, t.sumTime) as sumTime,   
                      if(t.pureTime is null, 0, t.pureTime) as pureTime
                    from activity_learn_daily ald
                    left join {$table} t on ald.activityId  =  t.activityId  and  ald.userId  =  t.userId  and  ald.dayTime  =  t.dayTime 
                    where ald.mediaType = 'video' LIMIT {$start}, {$limit};";
            $data = $this->biz['db']->fetchAll($sql);
            if (!empty($data)) {
                $this->getActivityLearnDailyDao()->batchUpdate(ArrayToolkit::column($data, 'id'), $data);
            }
        }
    }

    protected function refreshCourseTaskResult()
    {
        $count = $this->biz['db']->fetchAssoc("select count(*) from course_task_result ctr left join activity a on ctr.activityId = a.id where a.mediaType = 'video';");
        $limit = 1000;
        $totalPage = $count / $limit;
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $updateData = [];
            $sql = "select ctr.id, ctr.activityId, ctr.userId, ctr.stayTime from course_task_result ctr left join activity a on ctr.activityId = a.id where a.mediaType = 'video' limit {$start}, {$limit};";
            $data = $this->biz['db']->fetchAll($sql);
            if ('page' == $this->setting) {
                foreach ($data as $result) {
                    $updateData[] = ['id' => $result['id'], 'sumTime' => $result['stayTime']];
                }
            } else {
                $activityIds = ArrayToolkit::column($data, 'activityId');
                $userIds = ArrayToolkit::column($data, 'userId');
                $activityMarks = str_repeat('?,', count($activityIds) - 1).'?';
                $userMarks = str_repeat('?,', count($userIds) - 1).'?';
                $sql = "select id, activityId, userId, sumTime from activity_vide_daily where activityId in {$activityMarks} and userId in {$userMarks}";
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

                    $updateData[] = ['id' => $result['id'], 'sumTime' => array_sum(ArrayToolkit::column($userRecords[$result['activityId']], 'sumTime'))];
                }
            }

            if (!empty($updateData)) {
                $this->getActivityLearnDailyDao()->batchUpdate(ArrayToolkit::column($updateData, 'id'), $updateData);
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
