<?php

namespace Biz\Visualization\Job;

use Biz\System\Service\CacheService;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Dao\ActivityStayDailyDao;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateMediaTypeJob extends AbstractJob
{
    const LIMIT = 10000;

    const STAY_TABLE_REFRESH_PAGE = 'stay_refresh_page';

    const LEARN_TABLE_REFRESH_PAGE = 'learn_refresh_page';

    const CACHE_NAME = 'update_media_type';

    public function execute()
    {
        if (!$this->isFieldExist('activity_learn_daily', 'mediaType')) {
            $this->biz['db']->exec("ALTER TABLE `activity_learn_daily` ADD COLUMN `mediaType` varchar(32) NOT NULL DEFAULT '' COMMENT '教学活动类型' AFTER `courseSetId`;");
        }

        $data = $this->getCacheService()->gets([self::STAY_TABLE_REFRESH_PAGE, self::LEARN_TABLE_REFRESH_PAGE]);
        $this->updateActivityStayDaily($data);
        $this->updateActivityLearnDaily($data);

        $this->getCacheService()->clear(self::CACHE_NAME);
    }

    protected function updateActivityStayDaily($data)
    {
        $count = $this->biz['db']->fetchColumn('select count(*) from activity_stay_daily;');

        $limit = self::LIMIT;
        $totalPage = $count / $limit;
        $page = empty($data[self::STAY_TABLE_REFRESH_PAGE]) ? 0 : $data[self::STAY_TABLE_REFRESH_PAGE];
        for (; $page <= $totalPage; ++$page) {
            $this->getCacheService()->set(self::STAY_TABLE_REFRESH_PAGE, $page);
            $start = $page * $limit;
            $learnData = $this->biz['db']->fetchAll("select id from activity_stay_daily order by id ASC limit {$start}, {$limit}");
            if (empty($learnData)) {
                continue;
            }

            $marks = str_repeat('?,', count($learnData) - 1).'?';
            $sql = "select asd.id as id, if(a.mediaType is not null, a.mediaType, '') as mediaType from activity_stay_daily asd left join activity a on asd.activityId = a.id where asd.id in ({$marks})";
            $data = $this->biz['db']->fetchAll($sql, array_column($learnData, 'id'));
            empty($data) ? null : $this->getActivityStayDailyDao()->batchUpdate(array_column($data, 'id'), $data);
        }
        $this->getCacheService()->clear(self::STAY_TABLE_REFRESH_PAGE);
    }

    protected function updateActivityLearnDaily($data)
    {
        $count = $this->biz['db']->fetchColumn('select count(*) from activity_learn_daily;');

        $limit = self::LIMIT;
        $totalPage = $count / $limit;
        $page = empty($data[self::LEARN_TABLE_REFRESH_PAGE]) ? 0 : $data[self::LEARN_TABLE_REFRESH_PAGE];
        for (; $page <= $totalPage; ++$page) {
            $this->getCacheService()->set(self::LEARN_TABLE_REFRESH_PAGE, $page);
            $start = $page * $limit;
            $learnData = $this->biz['db']->fetchAll("select id from activity_learn_daily order by id ASC limit {$start}, {$limit}");
            if (empty($learnData)) {
                continue;
            }

            $marks = str_repeat('?,', count($learnData) - 1).'?';
            $sql = "select ald.id as id, if(a.mediaType is not null, a.mediaType, '') as mediaType from activity_learn_daily ald left join activity a on ald.activityId = a.id where ald.id in ({$marks})";
            $data = $this->biz['db']->fetchAll($sql, array_column($learnData, 'id'));
            empty($data) ? null : $this->getActivityLearnDailyDao()->batchUpdate(array_column($data, 'id'), $data);
        }
        $this->getCacheService()->clear(self::LEARN_TABLE_REFRESH_PAGE);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->biz->service('System:CacheService');
    }

    /**
     * @return ActivityLearnDailyDao
     */
    protected function getActivityLearnDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityLearnDailyDao');
    }

    /**
     * @return ActivityStayDailyDao
     */
    protected function getActivityStayDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityStayDailyDao');
    }
}
