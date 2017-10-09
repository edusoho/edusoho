<?php

namespace Codeages\Biz\Framework\Session\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Session\Dao\SessionDao;

class SessionDaoImpl extends GeneralDaoImpl implements SessionDao
{
    protected $table = 'biz_session';

    protected $table2 = 'sessions';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'sess_time'),
            'orderbys' => array(),
            'serializes' => array(),
            'conditions' => array(),
        );
    }


    public function getBySessId($sessId)
    {
        $session = null;
        if ($this->isTableExist($this->table)) {
            $sql = "SELECT * FROM {$this->table} WHERE sess_id = ?  LIMIT 1";
            $session = $this->db()->fetchAssoc($sql, array($sessId));
        }

        if (empty($session)) {
            $sql = "SELECT * FROM {$this->table2} WHERE sess_id = ?  LIMIT 1";
            $session = $this->db()->fetchAssoc($sql, array($sessId));
        }
        return $session;
    }

    public function deleteBySessId($sessId)
    {
        $sql = "DELETE FROM {$this->table} WHERE sess_id = ?";

        return $this->db()->executeUpdate($sql, array($sessId));
    }

    public function deleteBySessDeadlineLessThan($sessDeadline)
    {
        $sql = "DELETE FROM {$this->table} WHERE sess_deadline < ?";

        return $this->db()->executeUpdate($sql, array($sessDeadline));
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->db()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
