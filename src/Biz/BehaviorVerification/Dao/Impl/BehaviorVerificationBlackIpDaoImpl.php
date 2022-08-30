<?php

namespace Biz\BehaviorVerification\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\BehaviorVerification\Dao\BehaviorVerificationBlackIpDao;

class BehaviorVerificationBlackIpDaoImpl extends GeneralDaoImpl implements BehaviorVerificationBlackIpDao
{
    protected $table = 'behavior_verification_black_ip';

    public function getByIp($ip)
    {
        $sql = "SELECT * FROM {$this->table} where ip=?";

        return $this->db()->fetchAssoc($sql, [$ip]);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array('id', 'created_time', 'updated_time'),
            'conditions' => array(),
        );
    }
}
