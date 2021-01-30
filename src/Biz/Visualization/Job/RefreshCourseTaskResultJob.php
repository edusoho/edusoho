<?php

namespace Biz\Visualization\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\Task\Dao\TaskResultDao;

class RefreshCourseTaskResultJob extends BaseRefreshJob
{
    const REFRESH_TYPE = 'task_result';

    const CACHE_NAME = 'refresh_task_result';

    const LIMIT = 10000;

    public function execute()
    {
        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        if ('page' == $statisticsSetting['statistical_dimension']) {
            $this->refreshCourseTaskResultWhenPageSetting();
        } else {
            $this->refreshCourseTaskResultWhenPlayingSetting();
        }

        $this->getCacheService()->clear(self::CACHE_NAME);
    }

    protected function refreshCourseTaskResultWhenPageSetting()
    {
        $count = $this->biz['db']->fetchColumn("SELECT COUNT(*) FROM course_task_result ctr LEFT JOIN activity a ON ctr.activityId = a.id WHERE a.mediaType = 'video';");
        $limit = self::LIMIT;
        $totalPage = $count / $limit;
        for ($page = 0; $page <= $totalPage; ++$page) {
            $start = $page * $limit;
            $results = $this->biz['db']->fetchAll("select id from course_task_result order by id ASC limit {$start}, {$limit};");
            if (empty($results)) {
                continue;
            }

            $marks = str_repeat('?,', count($results) - 1).'?';
            $sql = "SELECT ctr.id AS id, IF (ctr.stayTime, ctr.stayTime, 0) AS sumTime FROM course_task_result ctr LEFT JOIN activity a ON ctr.activityId = a.id WHERE a.mediaType = 'video' and ctr.id in ({$marks});";
            $data = $this->biz['db']->fetchAll($sql, array_column($results, 'id'));

            if (!empty($data)) {
                $this->getTaskResultDao()->batchUpdate(array_column($data, 'id'), $data);
            }
            $this->getLogger()->addInfo("从{$start}刷新course_task_result结束");
        }
    }

    protected function refreshCourseTaskResultWhenPlayingSetting()
    {
        $limit = 100;
        $count = $this->biz['db']->fetchColumn('select count(*) from user;');
        $totalPage = $count / $limit;
        for ($page = 0; $page <= $totalPage; ++$page) {
            $updateData = [];
            $start = $page * $limit;
            $users = $this->biz['db']->fetchAll("select id from user order by id ASC limit {$start}, {$limit};");
            if (empty($users)) {
                continue;
            }

            $marks = str_repeat('?,', count($users) - 1).'?';
            $results = $this->biz['db']->fetchAll("select id, userId, activityId from course_task_result where userId in ({$marks})", array_column($users, 'id'));
            $records = $this->biz['db']->fetchAll("SELECT id, activityId, userId, sumTime FROM activity_video_daily WHERE userId in ({$marks})", array_column($users, 'id'));
            $records = ArrayToolkit::group($records, 'userId');
            foreach ($results as $result) {
                if (empty($records[$result['userId']])) {
                    continue;
                }

                $userRecords = ArrayToolkit::group($records[$result['userId']], 'activityId');
                if (empty($userRecords[$result['activityId']])) {
                    continue;
                }

                $sumTime = array_sum(array_column($userRecords[$result['activityId']], 'sumTime'));
                $updateData[] = ['id' => $result['id'], 'sumTime' => $sumTime];
            }
            if (!empty($updateData)) {
                $this->getTaskResultDao()->batchUpdate(array_column($updateData, 'id'), $updateData);
            }
            $this->getLogger()->addInfo("刷新从{$start}开始的user的course_task_result结束");
        }
    }

    /**
     * @return TaskResultDao
     */
    protected function getTaskResultDao()
    {
        return $this->biz->dao('Task:TaskResultDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
