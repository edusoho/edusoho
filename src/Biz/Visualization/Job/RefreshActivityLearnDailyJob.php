<?php

namespace Biz\Visualization\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\Task\Dao\TaskResultDao;
use Biz\Task\Service\TaskResultService;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;

class RefreshActivityLearnDailyJob extends BaseRefreshJob
{
    const REFRESH_TYPE = 'activity';

    const CACHE_NAME = 'refresh_activity';

    const LIMIT = 10000;

    protected $setting = '';

    public function execute()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $this->setting = $statisticsSetting['statistical_dimension'];
        $this->refreshActivityLearnDaily();
        $this->refreshCourseTaskResult();

        $this->getCacheService()->clear(self::CACHE_NAME);
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
            $this->getLogger()->addInfo("从{$start}刷新activity_learn_daily结束");
        }
    }

    protected function refreshCourseTaskResult()
    {
        if ('page' == $this->setting) {
            $this->refreshCourseTaskResultWhenPageSetting();
        } else {
            $this->refreshCourseTaskResultWhenPlayingSetting();
        }
    }

    protected function refreshCourseTaskResultWhenPageSetting()
    {
        $count = $this->biz['db']->fetchColumn("SELECT COUNT(*) FROM course_task_result ctr LEFT JOIN activity a ON ctr.activityId = a.id WHERE a.mediaType = 'video';");
        $limit = self::LIMIT;
        $totalPage = $count / $limit;
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $sql = "SELECT ctr.id AS id, IF (ctr.stayTime, ctr.stayTime, 0) AS sumTime FROM course_task_result ctr LEFT JOIN activity a ON ctr.activityId = a.id WHERE a.mediaType = 'video' LIMIT  {$start}, {$limit};";
            $data = $this->biz['db']->fetchAll($sql);

            if (!empty($data)) {
                $this->getTaskResultDao()->batchUpdate(array_column($data, 'id'), $data);
            }
            $this->getLogger()->addInfo("从{$start}刷新course_task_result结束");
        }
    }

    protected function refreshCourseTaskResultWhenPlayingSetting()
    {
        $users = $this->biz['db']->fetchAll('select id from user;');
        foreach ($users as $user) {
            $updateData = [];
            $records = $this->biz['db']->fetchAll('SELECT id, activityId, userId, sumTime FROM activity_video_daily WHERE userId = ?', [$user['id']]);
            $results = $this->biz['db']->fetchAll('select id, userId, activityId from course_task_result where userId = ?', [$user['id']]);
            $records = ArrayToolkit::group($records, 'activityId');
            foreach ($results as $result) {
                if (empty($records[$result['activityId']])) {
                    continue;
                }

                $sumTime = array_sum(array_column($records[$result['activityId']], 'sumTime'));
                $updateData[] = ['id' => $result['id'], 'sumTime' => $sumTime];
            }
            if (!empty($updateData)) {
                $this->getTaskResultDao()->batchUpdate(array_column($updateData, 'id'), $updateData);
            }
            $this->getLogger()->addInfo("刷新{$user['id']}的course_task_result结束");
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

    /**
     * @return TaskResultDao
     */
    protected function getTaskResultDao()
    {
        return $this->biz->dao('Task:TaskResultDao');
    }
}
