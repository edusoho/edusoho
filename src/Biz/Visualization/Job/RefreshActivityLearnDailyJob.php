<?php

namespace Biz\Visualization\Job;

use Biz\System\Service\SettingService;
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
            $learnData = $this->biz['db']->fetchAll("select id from activity_learn_daily order by id ASC limit {$start}, {$limit}");
            if (empty($learnData)) {
                continue;
            }

            $marks = str_repeat('?,', count($learnData) - 1).'?';
            $sql = "
                SELECT  ald.id AS id,  
                    IF(t.sumTime, t.sumTime, 0) AS sumTime, 
                    IF(t.pureTime, t.pureTime, 0) AS pureTime
                FROM activity_learn_daily ald
                LEFT JOIN {$table} t 
                ON ald.activityId  =  t.activityId  AND  ald.userId  =  t.userId  AND  ald.dayTime = t.dayTime 
                WHERE ald.mediaType = 'video' and ald.id in ({$marks});
            ";
            $data = $this->biz['db']->fetchAll($sql, array_column($learnData, 'id'));
            if (!empty($data)) {
                $this->getActivityLearnDailyDao()->batchUpdate(array_column($data, 'id'), $data);
            }
            $this->getLogger()->addInfo("从{$start}刷新activity_learn_daily结束");
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
}
