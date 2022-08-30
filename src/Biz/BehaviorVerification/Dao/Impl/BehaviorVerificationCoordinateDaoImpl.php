<?php

namespace Biz\BehaviorVerification\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\BehaviorVerification\Dao\BehaviorVerificationCoordinateDao;

class BehaviorVerificationCoordinateDaoImpl extends GeneralDaoImpl implements BehaviorVerificationCoordinateDao
{
    protected $table = 'behavior_verification_coordinate';

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
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array('id', 'hit_counts', 'created_time', 'updated_time'),
            'conditions' => array(),
        );
    }
}