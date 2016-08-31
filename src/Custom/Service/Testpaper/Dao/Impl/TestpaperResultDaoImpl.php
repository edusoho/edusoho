<?php

namespace Custom\Service\Testpaper\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Testpaper\Dao\TestpaperResultDao;

class TestpaperResultDaoImpl extends BaseDao implements TestpaperResultDao
{
    protected $table = 'testpaper_result';

    public function findTestPaperResultCountByStatusAndTestIdsAndOrgId($ids, $status, $orgId)
    {
        if (empty($ids)) {
            return null;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        array_push($ids, $status);

        $sql = "SELECT COUNT(*) FROM {$this->table} INNER JOIN user ON {$this->table}.userId = user.id WHERE `testId` IN ({$marks}) AND `status` = ?";

        if (!empty($orgId)) {
            $sql .= " AND orgId = ?";
            array_push($ids, $orgId);
        }
        return $this->getConnection()->fetchColumn($sql, $ids);
    }

    public function findTestPaperResultsByStatusAndTestIdsAndOrgId($ids, $status, $orgId)
    {
        if (empty($ids)) {
            return null;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        array_push($ids, $status);

        $sql = "SELECT * FROM {$this->table} INNER JOIN user ON {$this->table}.userId = user.id WHERE `testId` IN ({$marks}) AND `status` = ?";

        if (!empty($orgId)) {
            $sql .= " AND orgId = ?";
            array_push($ids, $orgId);
        }
        return $this->getConnection()->fetchAll($sql, $ids);
    }
}