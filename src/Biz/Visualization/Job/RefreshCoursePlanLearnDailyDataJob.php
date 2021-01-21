<?php

namespace Biz\Visualization\Job;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshCoursePlanLearnDailyDataJob extends AbstractJob
{
    const LIMIT = 10000;

    public function execute()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        if (empty($statisticsSetting) || 'playing' == $statisticsSetting['statistical_dimension']) {
            // $this->refreshActivityLearnDaily('activity_video_daily');
        } else {
            // $this->refreshActivityLearnDaily('activity_stay_daily');
        }
    }

    protected function refreshByStayDaily()
    {
        $limit = self::LIMIT;
        $totalPage = ceil($this->biz['db']->fetchColumn('SELECT COUNT(id) FROM `course_plan_learn_daily`') / $limit);
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $ids = $this->biz['db']->fetchAll("SELECT `id` FROM `course_plan_learn_daily` LIMIT $start, $limit");
            $ids = array_column($ids, 'id');
            $marks = str_repeat('?,', count($ids) - 1).'?';

            $this->biz['db']->executeUpdate("
                UPDATE `course_plan_learn_daily` l INNER JOIN (
                    SELECT userId, dayTime, courseId, sum(sumTime) AS sumTime
                    FROM `course_plan_stay_daily` GROUP BY userId, courseId, dayTime) AS s
                    ON l.dayTime = s.dayTime AND l.userId = s.userId AND l.courseId = s.courseId AND l.id IN ({$marks})
                SET l.sumTime = s.sumTime;
            ", $ids);
        }
    }

    protected function refreshByVideoDaily()
    {
        $limit = self::LIMIT;
        $totalPage = ceil($this->biz['db']->fetchColumn('SELECT COUNT(id) FROM `course_plan_learn_daily`') / $limit);
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $ids = $this->biz['db']->fetchAll("SELECT `id` FROM `course_plan_learn_daily` LIMIT $start, $limit");
            $ids = array_column($ids, 'id');
            $marks = str_repeat('?,', count($ids) - 1).'?';
            $this->biz['db']->executeUpdate("UPDATE `course_plan_learn_daily` SET sumTime = 0 WHERE id IN ({$marks});", $ids);
            $this->biz['db']->executeUpdate("
                UPDATE `course_plan_learn_daily` l INNER JOIN (
                    SELECT userId, dayTime, courseId, sum(sumTime) AS sumTime
                    FROM `course_plan_video_daily` GROUP BY userId, courseId, dayTime) AS s
                    ON l.dayTime = s.dayTime AND l.userId = s.userId AND l.courseId = s.courseId AND l.id IN ({$marks})
                SET l.sumTime = s.sumTime;
            ", $ids);

            $this->biz['db']->executeUpdate("
                UPDATE `course_plan_learn_daily` l INNER JOIN (
                    SELECT userId, dayTime, courseId, sum(sumTime) AS sumTime
                    FROM `activity_stay_daily` WHERE mediaType != 'video' GROUP BY userId, courseId, dayTime) AS s
                    ON l.dayTime = s.dayTime AND l.userId = s.userId AND l.courseId = s.courseId AND l.id IN ({$marks})
                SET l.sumTime = l.sumTime + s.sumTime;
            ", $ids);
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
