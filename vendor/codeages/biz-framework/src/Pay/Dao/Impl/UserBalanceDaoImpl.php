<?php

namespace Codeages\Biz\Framework\Pay\Dao\Impl;

use Codeages\Biz\Framework\Pay\Dao\UserBalanceDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserBalanceDaoImpl extends GeneralDaoImpl implements UserBalanceDao
{
    protected $table = 'biz_user_balance';

    public function getByUserId($userId)
    {
        return $this->getByFields(array(
            'user_id' => $userId
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
        );
    }
}