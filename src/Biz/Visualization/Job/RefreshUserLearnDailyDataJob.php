<?php

namespace Biz\Visualization\Job;

use Biz\System\Service\SettingService;
use Biz\Visualization\Dao\UserLearnDailyDao;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshUserLearnDailyDataJob extends AbstractJob
{
    const LIMIT = 10000;

    public function execute()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        if (empty($statisticsSetting) || 'playing' == $statisticsSetting['statistical_dimension']) {
            $this->refreshByWatchDaily();
        } else {
            $this->refreshByStayDaily();
        }
    }

    protected function refreshByStayDaily()
    {
        $limit = self::LIMIT;
        $totalPage = ceil($this->biz['db']->fetchColumn('SELECT COUNT(id) FROM `user_learn_daily`') / $limit);
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;

            $updateFields = $this->biz['db']->fetchAll("
            SELECT l.id AS id, s.sumTime AS sumTime FROM `user_learn_daily` l LEFT JOIN (
                SELECT userId, dayTime, sum(sumTime) AS sumTime
                FROM `user_stay_daily` GROUP BY userId, dayTime) AS s
                ON l.dayTime = s.dayTime AND l.userId = s.userId LIMIT {$start}, {$limit};
            ");

            if (empty($updateFields)) {
                continue;
            }
            $this->getUserLearnDailyDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields);
        }
    }

    protected function refreshByWatchDaily()
    {
        $limit = self::LIMIT;
        $totalPage = ceil($this->biz['db']->fetchColumn('SELECT COUNT(id) FROM `user_learn_daily`') / $limit);
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $watchData = $this->biz['db']->fetchAll("
            SELECT l.id AS id, s.sumTime AS sumTime FROM user_learn_daily l INNER JOIN (
                SELECT userId, dayTime, sum(sumTime) AS sumTime FROM user_video_daily GROUP BY userId, dayTime
            ) AS s ON l.dayTime = s.dayTime AND l.userId = s.userId LIMIT {$start}, {$limit}
        ");
            $watchData = array_column($watchData, null, 'id');

            $stayData = $this->biz['db']->fetchAll("
            SELECT l.id AS id, s.sumTime AS sumTime FROM user_learn_daily l INNER JOIN (
                SELECT userId, dayTime, sum(sumTime) AS sumTime FROM activity_stay_daily WHERE mediaType != 'video' GROUP BY userId, dayTime
            ) AS s ON l.dayTime = s.dayTime AND l.userId = s.userId LIMIT {$start}, {$limit}
        ");
            $stayData = array_column($stayData, null, 'id');
            array_walk($stayData, function (&$data) use (&$watchData) {
                $data['sumTime'] += empty($watchData[$data['id']]) ? 0 : $watchData[$data['id']]['sumTime'];
                unset($watchData[$data['id']]);
            });

            $updateFields = array_merge($stayData, $watchData);
            if (empty($updateFields)) {
                continue;
            }

            $this->getUserLearnDailyDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields);
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return UserLearnDailyDao
     */
    protected function getUserLearnDailyDao()
    {
        return $this->biz->dao('Visualization:UserLearnDailyDao');
    }
}
