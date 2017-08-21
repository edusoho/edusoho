<?php

namespace Codeages\Biz\Framework\Pay\Dao\Impl;

use Codeages\Biz\Framework\Pay\Dao\SiteCashflowDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SiteCashflowDaoImpl extends GeneralDaoImpl implements SiteCashflowDao
{
    protected $table = 'biz_site_cashflow';

    public function findByTradeSn($tradeSn)
    {
        return $this->findByFields(array(
            'trade_sn' => $tradeSn
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
        );
    }
}