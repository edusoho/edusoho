<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\SessionDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SessionDaoImpl extends GeneralDaoImpl implements SessionDao
{
    protected $table = "sessions";

    public function getByUserId($userId)
    {
        return $this->getByFields(array('sess_user_id' => $userId));
    }

    public function deleteByUserId($userId)
    {
        return $this->db()->delete($this->table(), array('sess_user_id' => $userId));
    }

    public function getOnlineCount($retentionTime)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `sess_time`  >= (unix_timestamp(now()) - ?);";
        return $this->db()->fetchColumn($sql, array($retentionTime)) ?: null;
    }

    public function getLoginCount($retentionTime)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `sess_time` >= (unix_timestamp(now())-?) AND `sess_user_id` > 0";
        return $this->db()->fetchColumn($sql, array($retentionTime)) ?: null;
    }

    public function findBySessionTime($sessionTime, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `sess_time` < ? LIMIT {$limit};";
        return $this->db()->fetchAll($sql, array($sessionTime));
    }

    public function deleteByIds($ids)
    {
        if (empty($ids)) {
            return 0;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "DELETE FROM {$this->table} WHERE `sessi_id` in ( {$marks} );";

        return $this->getConnection()->executeUpdate($sql, $ids);
    }

    public function declares()
    {
    }
}
