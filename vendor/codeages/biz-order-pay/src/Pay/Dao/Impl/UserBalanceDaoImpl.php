<?php

namespace Codeages\Biz\Pay\Dao\Impl;

use Codeages\Biz\Pay\Dao\UserBalanceDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserBalanceDaoImpl extends GeneralDaoImpl implements UserBalanceDao
{
    protected $table = 'biz_pay_user_balance';

    public function getByUserId($userId)
    {
        return $this->getByFields(array(
            'user_id' => $userId
        ));
    }

    public function findByUserIds($userIds)
    {
        return $this->findInField('user_id', $userIds);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array(
                'id',
                'created_time',
                'amount',
                'cash_amount',
                'recharge_amount',
                'purchase_amount',
            ),
            'conditions' => array(
                'user_id IN (:user_ids)',
                'user_id != :except_user_id',
                'user_id not in (:except_user_ids)'
            ),
        );
    }
}