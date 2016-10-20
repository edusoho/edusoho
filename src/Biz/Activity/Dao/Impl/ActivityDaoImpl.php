<?php

namespace Biz\Activity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Activity\Dao\ActivityDao;

class ActivityDaoImpl extends GeneralDaoImpl implements ActivityDao
{
    protected $table = 'activity';

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? LIMIT 1";
        return $this->db()->fetchAll($sql, array($courseId)) ? : array();
    }

    public function declares()
    {

    }


}
