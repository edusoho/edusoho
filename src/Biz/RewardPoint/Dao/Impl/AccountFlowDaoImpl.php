<?php

namespace Biz\RewardPoint\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RewardPoint\Dao\AccountFlowDao;

class AccountFlowDaoImpl extends GeneralDaoImpl implements AccountFlowDao
{
    protected $table = 'reward_point_account_flow';

    public function sumInflowByUserIdAndWayAndTime($userId, $way, $startTime, $endTime)
    {
        $sql = "SELECT sum(amount) FROM {$this->table} where userId = ? and way = ? and type = ? and createdTime >= ? and createdTime <= ?";

        return $this->db()->fetchColumn($sql, array($userId, $way, 'inflow', $startTime, $endTime));
    }

    public function sumInflowByUserId($userId)
    {
        $sql = "SELECT sum(amount) FROM {$this->table} where userId = ?";

        return $this->db()->fetchColumn($sql, array($userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'userId = :userId',
                'userId IN ( :userIds)',
                'type = :type',
                'way = :way',
                'operator = :operator',
                'createdTime >= :startTime',
                'createdTime < :endTime',
            ),
        );
    }
}
