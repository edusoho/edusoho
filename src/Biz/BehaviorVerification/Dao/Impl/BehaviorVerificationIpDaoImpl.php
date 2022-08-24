<?php

namespace Biz\BehaviorVerification\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\BehaviorVerification\Dao\BehaviorVerificationIpDao;

class BehaviorVerificationIpDaoImpl extends GeneralDaoImpl implements BehaviorVerificationIpDao
{
    protected $table = 'behavior_verification_ip';

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
            'conditions' => array(

            ),
        );
    }
}
