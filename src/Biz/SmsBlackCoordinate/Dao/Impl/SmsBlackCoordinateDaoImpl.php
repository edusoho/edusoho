<?php

namespace Biz\SmsBlackCoordinate\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\SmsBlackCoordinate\Dao\SmsBlackCoordinateDao;

class SmsBlackCoordinateDaoImpl extends GeneralDaoImpl implements SmsBlackCoordinateDao
{
    protected $table = 'sms_black_coordinate';

    public function getByCoordinate($coordinate)
    {
        $sql = "SELECT * FROM {$this->table} where coordinate=?";

        return $this->db()->fetchAssoc($sql, [$coordinate]);
    }

    public function getTop10()
    {
        $sql = "SELECT * FROM {$this->table} order by hit_counts desc limit 10";

        return $this->db()->fetchAssoc($sql);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'hit_counts', 'createdTime', 'updatedTime'),
            'conditions' => array(

            ),
        );
    }
}