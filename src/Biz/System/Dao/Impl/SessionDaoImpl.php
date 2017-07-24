<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\SessionDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SessionDaoImpl extends GeneralDaoImpl implements SessionDao
{
    protected $table = 'sessions';

    public function getByUserId($userId)
    {
        return $this->getByFields(array('sess_user_id' => $userId));
    }

    public function deleteByUserId($userId)
    {
        return $this->db()->delete($this->table(), array('sess_user_id' => $userId));
    }

    public function countOnline($retentionTime)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `sess_time`  >= ?;";

        return $this->db()->fetchColumn($sql, array(time() - $retentionTime)) ?: null;
    }

    public function countLogin($retentionTime)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `sess_time` >= ? AND `sess_user_id` > 0";

        return $this->db()->fetchColumn($sql, array(time() - $retentionTime)) ?: null;
    }

    public function searchBySessionTime($sessionTime, $limit)
    {
        $limit = (int) $limit;
        $sql = "SELECT * FROM {$this->table} WHERE `sess_time` < ? LIMIT {$limit};";

        return $this->db()->fetchAll($sql, array($sessionTime));
    }

    public function deleteByIds($ids)
    {
        if (empty($ids)) {
            return 0;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "DELETE FROM {$this->table} WHERE `sess_id` in ( {$marks} );";

        return $this->db()->executeUpdate($sql, $ids);
    }

    public function declares()
    {
    }
}
