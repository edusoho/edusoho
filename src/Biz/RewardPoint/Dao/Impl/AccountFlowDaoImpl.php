<?php

namespace Biz\RewardPoint\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RewardPoint\Dao\AccountFlowDao;

class AccountFlowDaoImpl extends GeneralDaoImpl implements AccountFlowDao
{
    protected $table = 'reward_point_account_flow';

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
