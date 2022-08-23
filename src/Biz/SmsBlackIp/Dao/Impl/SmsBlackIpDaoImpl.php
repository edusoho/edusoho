<?php

namespace Biz\SmsBlackIp\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\SmsBlackIp\Dao\SmsBlackIpDao;

class SmsBlackIpDaoImpl extends GeneralDaoImpl implements SmsBlackIpDao
{
    protected $table = 'sms_black_ip';

    public function getByIp($ip)
    {
        $sql = "SELECT * FROM {$this->table} where ip=?";

        return $this->db()->fetchAssoc($sql, [$ip]);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(

            ),
        );
    }
}
