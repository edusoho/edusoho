<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\SessionDao;

class SessionDaoImpl extends BaseDao implements SessionDao
{
    protected $table = "sessions";

    public function get($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getSessionByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sess_user_id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ?: null;
    }

    public function delete($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteSessionByUserId($userId)
    {
        return $this->getConnection()->delete($this->table, array('sess_user_id' => $userId));
    }

    public function getOnlineCount($retentionTime)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `sess_time`  >= (unix_timestamp(now()) - ?);";
        //$sql = "SELECT COUNT(*) FROM {$this->table} WHERE `sess_time`  BETWEEN (unix_timestamp(now()) - ?) AND (unix_timestamp(now()));";
        return $this->getConnection()->fetchColumn($sql, array($retentionTime)) ?: null;
    }

    public function getLoginCount($retentionTime)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `sess_time` >= (unix_timestamp(now())-?) AND `sess_user_id` > 0";
        return $this->getConnection()->fetchColumn($sql, array($retentionTime)) ?: null;
    }

    public function findSessionsBySessionTime($sessionTime, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `sess_time` < ? LIMIT {$limit};";
        return $this->getConnection()->fetchAll($sql, array($sessionTime));
    }

    public function deleteSessionsByIds($ids)
    {
        if (empty($ids)) {
            return 0;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "DELETE FROM {$this->table} WHERE `sessi_id` in ( {$marks} );";

        return $this->getConnection()->executeUpdate($sql, $ids);
    }
}
