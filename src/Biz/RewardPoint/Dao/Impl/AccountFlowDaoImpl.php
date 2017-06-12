<?php

namespace Biz\RewardPoint\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RewardPoint\Dao\AccountFlowDao;

class AccountFlowDaoImpl extends GeneralDaoImpl implements AccountFlowDao
{
    protected $table = 'reward_point_account_flow';

    public function getInflowByUserIdAndTarget($userId, $targetId, $targetType)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? and targetId = ? and targetType = ? and type = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($userId, $targetId, $targetType, 'inflow')) ?: null;
    }

    public function sumAccountOutFlowByUserId($userId)
    {
        $sql = "SELECT sum(amount) FROM `reward_point_account_flow` WHERE  `type` = 'outflow' AND `userId` =?";

        return $this->db()->fetchColumn($sql, array($userId));
    }

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
