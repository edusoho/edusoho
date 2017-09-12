<?php

namespace Codeages\Biz\Framework\Pay\Dao\Impl;

use Codeages\Biz\Framework\Pay\Dao\UserCashflowDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserCashflowDaoImpl extends GeneralDaoImpl implements UserCashflowDao
{
    protected $table = 'biz_user_cashflow';

    public function findByTradeSn($sn)
    {
        return $this->findByFields(array('trade_sn' => $sn));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time')
        );
    }
}