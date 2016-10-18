<?php

namespace Activity\Service\Activity\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Activity\Service\Activity\Dao\ActivityLearnLogDao;

class ActivityLearnLogDaoImpl extends BaseDao implements ActivityLearnLogDao
{
    protected $table = 'activity_learn_log';

    public function get($id)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function add($log)
    {
        $log['createdTime'] = time();
        $affected           = $this->getConnection()->insert($this->table, $log);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert log error.');
        }

        return $this->get($this->getConnection()->lastInsertId());
    }
}
