<?php

namespace Biz\RewardPoint\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RewardPoint\Dao\AccountFlowDao;

class AccountFlowDaoImpl extends GeneralDaoImpl implements AccountFlowDao
{
    protected $table = 'reward_point_account_flow';

    public function sumAccountOutFlowByUserId($userId)
    {
        $sql = "SELECT sum(amount) FROM `reward_point_account_flow` WHERE  `type` = 'outflow' AND `userId` =?";

        return $this->db()->fetchColumn($sql,array($userId));
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
                'operator = :operator',
            ),
        );
    }
}
