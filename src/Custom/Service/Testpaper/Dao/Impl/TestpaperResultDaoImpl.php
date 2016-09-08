<?php

namespace Custom\Service\Testpaper\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Testpaper\Dao\TestpaperResultDao;

class TestpaperResultDaoImpl extends BaseDao implements TestpaperResultDao
{
    protected $table = 'testpaper_result';

    public function findTestPaperResultCountByStatusAndTestIdsAndUserIds($ids, $status, $userIds)
    {
        if (empty($ids)) {
            return null;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $userIdMarks = str_repeat('?,', count($userIds) - 1).'?';

        array_push($ids, $status);
        $params = array_merge($ids, $userIds);

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `testId` IN ({$marks}) AND `status` = ? AND `userId` IN ({$userIdMarks})";

        return $this->getConnection()->fetchColumn($sql, $params);
    }

    public function findTestPaperResultsByStatusAndTestIdsAndUserIds($ids, $status, $userIds)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $userIdMarks = str_repeat('?,', count($userIds) - 1).'?';

        array_push($ids, $status);
        $params = array_merge($ids, $userIds);

        $sql = "SELECT * FROM {$this->table} WHERE `testId` IN ({$marks}) AND `status` = ? AND `userId` IN ({$userIdMarks})";

        return $this->getConnection()->fetchAll($sql, $params)?: array();
    }
}