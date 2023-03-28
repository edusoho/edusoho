<?php

namespace Biz\SmsDefence\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\SmsDefence\Dao\SmsBlackListDao;

class SmsBlackListDaoImpl extends GeneralDaoImpl implements SmsBlackListDao
{
    protected $table = 'sms_black_list';

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
