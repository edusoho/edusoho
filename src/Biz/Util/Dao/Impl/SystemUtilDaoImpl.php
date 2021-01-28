<?php

namespace Biz\Util\Dao\Impl;

use Biz\Util\Dao\SystemUtilDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SystemUtilDaoImpl extends GeneralDaoImpl implements SystemUtilDao
{
    public function getCourseIdsWhereCourseHasDeleted()
    {
        $sql = 'SELECT DISTINCT  targetId FROM upload_files WHERE ';
        $sql .= " targetType='courselesson' AND usedCount<= 0 AND targetId NOT IN (SELECT id FROM course)";

        return $this->db()->fetchAll($sql);
    }

    public function declares()
    {
        return array();
    }
}
