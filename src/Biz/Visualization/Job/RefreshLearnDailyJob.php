<?php

namespace Biz\Visualization\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshLearnDailyJob extends AbstractJob
{
    const LIMIT = 10000;

    public function execute()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        if (empty($statisticsSetting) || 'playing' == $statisticsSetting['statistical_dimension']) {
            $this->refreshActivityLearnDaily('activity_video_daily');
        } else {
            $this->refreshActivityLearnDaily('activity_stay_daily');
        }
    }

    protected function refreshActivityLearnDaily($table)
    {
        $count = $this->getActivityLearnDailyDao()->count(['mediaType' => 'video']);
        $limit = self::LIMIT;
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
