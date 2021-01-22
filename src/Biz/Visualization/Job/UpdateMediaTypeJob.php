<?php

namespace Biz\Visualization\Job;

use Biz\System\Service\CacheService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateMediaTypeJob extends AbstractJob
{
    const LIMIT = 10000;

    const STAY_TABLE_REFRESH_PAGE = 'stay_refresh_page';

    const LEARN_TABLE_REFRESH_PAGE = 'learn_refresh_page';

    public function execute()
    {
        if (!$this->isFieldExist('activity_learn_daily', 'mediaType')) {
            $this->biz['db']->exec("ALTER TABLE `activity_learn_daily` ADD COLUMN `mediaType` varchar(32) NOT NULL DEFAULT '' COMMENT '教学活动类型' AFTER `courseSetId`;");
        }

        $data = $this->getCacheService()->gets([self::STAY_TABLE_REFRESH_PAGE, self::LEARN_TABLE_REFRESH_PAGE]);
        $this->updateActivityStayDaily($data);
        $this->updateActivityLearnDaily($data);
    }

    protected function updateActivityStayDaily($data)
    {
        $count = $this->biz['db']->fetchAssoc("select count(*) from activity_stay_daily;");
        $limit = self::LIMIT;
        $totalPage = $count / $limit;
        $page = empty($data[self::STAY_TABLE_REFRESH_PAGE]) ? 0 : $data[self::STAY_TABLE_REFRESH_PAGE];
        for (;$page <= $totalPage; ++$page) {
            $this->getCacheService()->set(self::STAY_TABLE_REFRESH_PAGE, $page);
            $start = $page * $limit;
            $sql = "update activity_stay_daily asd left join activity a on asd.activityId = a.id set asd.mediaType = case when a.mediaType is null then '' else a.mediaType limit {$start}, {$limit}";
            $this->biz['db']->exec($sql);
        }
        $this->getCacheService()->clear(self::STAY_TABLE_REFRESH_PAGE);
    }

    protected function updateActivityLearnDaily($data)
    {
        $count = $this->biz['db']->fetchAssoc("select count(*) from activity_learn_daily;");
        $limit = self::LIMIT;
        $totalPage = $count / $limit;
        $page = empty($data[self::LEARN_TABLE_REFRESH_PAGE]) ? 0 : $data[self::LEARN_TABLE_REFRESH_PAGE];
        for (;$page <= $totalPage; ++$page) {
            $this->getCacheService()->set(self::LEARN_TABLE_REFRESH_PAGE, $page);
            $start = $page * $limit;
            $sql = "update activity_learn_daily ald left join activity a on ald.activityId = a.id set ald.mediaType = case when a.mediaType is null then '' else a.mediaType limit {$start}, {$limit}";
            $this->biz['db']->exec($sql);
        }
        $this->getCacheService()->clear(self::LEARN_TABLE_REFRESH_PAGE);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->biz->service('System:CacheService');
    }
}
