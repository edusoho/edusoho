<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserFortuneLogDao;

class UserFortuneLogDaoImpl extends BaseDao implements UserFortuneLogDao
{
    protected $table = 'user_fortune_log';
    
    public function addLog(array $log)
    {
        $affected = $this->getConnection()->insert($this->table, $log);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert log error');
        }
        return $this->getLog($this->getConnection()->lastInsertId());
    }

    public function getLog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }
}