<?php

namespace Codeages\Biz\Framework\Session\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Session\Dao\SessionDao;

class SessionDaoImpl extends GeneralDaoImpl implements SessionDao
{
    protected $table = 'biz_session';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'sess_time'),
            'orderbys' => array('created_time', 'id', 'sess_time'),
            'serializes' => array(
            ),
            'conditions' => array(
                'sess_time < :gt_sess_time',
            ),
        );
    }

    public function getBySessId($sessId)
    {
        return $this->getByFields(array('sess_id' => $sessId));
    }

    public function deleteBySessId($sessId)
    {
        $sql = "DELETE FROM {$this->table} WHERE sess_id = ?";
        return $this->db()->executeUpdate($sql, array($sessId));
    }

    public function deleteByInvalid()
    {
        $sql = "DELETE FROM {$this->table} WHERE sess_time < (? - sess_lifetime) ";
        return $this->db()->executeUpdate($sql, array(time()));
    }

    public function countLogined($gtSessTime)
    {
        $sql = "SELECT count(*) FROM {$this->table} WHERE sess_time > ? AND sess_user_id > 0 ";
        return $this->db()->fetchColumn($sql, array($gtSessTime));
    }

    public function countTotal($gtSessTime)
    {
        $sql = "SELECT count(*) FROM {$this->table} WHERE sess_time > ? ";
        return $this->db()->fetchColumn($sql, array($gtSessTime));
    }
}