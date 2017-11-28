<?php

namespace Codeages\Biz\Framework\Session\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Session\Dao\OnlineDao;

class OnlineDaoImpl extends GeneralDaoImpl implements OnlineDao
{
    protected $table = 'biz_online';

    public function getBySessId($sessionId)
    {
        return $this->getByFields(array('sess_id' => $sessionId));
    }

    public function deleteByDeadlineLessThan($deadline)
    {
        $sql = "DELETE FROM {$this->table} WHERE deadline < ?";

        return $this->db()->executeUpdate($sql, array($deadline));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'active_time'),
            'orderbys' => array('active_time'),
            'serializes' => array(
            ),
            'conditions' => array(
                'active_time > :active_time_GT',
                'is_login = :is_login',
                'user_id = :user_id',
            ),
        );
    }
}
