<?php

namespace Biz\Visualization\Job;

use Biz\System\Service\SettingService;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;

class RefreshCoursePlanLearnDailyJob extends BaseRefreshJob
{
    const REFRESH_TYPE = 'course_plan';

    const CACHE_NAME = 'refresh_course_plan';

    const LIMIT = 10000;

    public function execute()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $totalPage = ceil($this->biz['db']->fetchColumn('SELECT COUNT(*) FROM `course_plan_learn_daily`') / self::LIMIT);

        for ($page = 0; $page < $totalPage; ++$page) {
            $start = $page * self::LIMIT;
            if (empty($statisticsSetting) || 'page' != $statisticsSetting['statistical_dimension']) {
                $this->refreshByWatchDaily($start, self::LIMIT);
            } else {
                $this->refreshByStayDaily($start, self::LIMIT);
            }
        }
        $this->getCacheService()->clear(self::CACHE_NAME);
    }

    protected function refreshByStayDaily($start, $limit)
    {
        $updateFields = $this->biz['db']->fetchAll("
            SELECT l.id AS id, IF(s.sumTime, s.sumTime, 0) AS sumTime FROM `course_plan_learn_daily` l 
                LEFT JOIN `course_plan_stay_daily` s 
                ON l.dayTime = s.dayTime AND l.userId = s.userId AND l.courseId = s.courseId LIMIT {$start}, {$limit};
        ");

        if (!empty($updateFields)) {
            $this->getCoursePlanLearnDailyDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields);
        }

        $this->getLogger()->addInfo("从{$start}刷新course_plan_learn_daily结束");
    }

    protected function refreshByWatchDaily($start, $limit)
    {
        $coursePlanLearnDailyIds = $this->biz['db']->fetchAll("SELECT id FROM course_plan_learn_daily LIMIT {$start}, {$limit}");
        $coursePlanLearnDailyIds = array_column($coursePlanLearnDailyIds, 'id');
        $marks = empty($coursePlanLearnDailyIds) ? '' : str_repeat('?,', count($coursePlanLearnDailyIds) - 1).'?';

        $watchData = empty($marks) ? [] : $this->biz['db']->fetchAll("
            SELECT l.id AS id, IF(s.sumTime, s.sumTime, 0) AS sumTime FROM course_plan_learn_daily l 
                INNER JOIN course_plan_video_daily s 
                ON l.dayTime = s.dayTime AND l.userId = s.userId AND l.courseId = s.courseId AND l.id IN ({$marks});
        ", $coursePlanLearnDailyIds);

        $watchData = array_column($watchData, null, 'id');

        $stayData = empty($marks) ? [] : $this->biz['db']->fetchAll("
            SELECT id, cld1.sumTime FROM course_plan_learn_daily cld INNER JOIN (
                SELECT l.userId AS userId, l.dayTime AS dayTime, l.courseId AS courseId, IF(sum(s.sumTime), sum(s.sumTime), 0) AS sumTime 
                    FROM course_plan_learn_daily l INNER JOIN activity_stay_daily s 
                    ON l.dayTime = s.dayTime AND l.userId = s.userId AND l.courseId = s.courseId 
                    WHERE s.mediaType != 'video' AND l.id IN ({$marks}) 
                    GROUP BY l.userId, l.dayTime, l.courseId
            ) AS cld1 ON cld.dayTime = cld1.dayTime AND cld.userId = cld1.userId AND cld.courseId = cld1.courseId;
        ", $coursePlanLearnDailyIds);

        $stayData = array_column($stayData, null, 'id');
        array_walk($stayData, function (&$data) use (&$watchData) {
            $data['sumTime'] += empty($watchData[$data['id']]) ? 0 : $watchData[$data['id']]['sumTime'];
            unset($watchData[$data['id']]);
        });

        $updateFields = array_merge($stayData, $watchData);

        if (!empty($updateFields)) {
            $this->getCoursePlanLearnDailyDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields);
        }

        $this->getLogger()->addInfo("从{$start}刷新course_plan_learn_daily结束");
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return CoursePlanLearnDailyDao
     */
    protected function getCoursePlanLearnDailyDao()
    {
        return $this->biz->dao('Visualization:CoursePlanLearnDailyDao');
    }
}
