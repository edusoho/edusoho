<?php

namespace Biz\Order\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Order\Dao\MoneyRecordsDao;

class MoneyRecordsDaoImpl extends GeneralDaoImpl implements MoneyRecordsDao
{
    protected $table = 'money_record';

    public function declares()
    {
        return array(
            'timestamps' => array(),
            'serializes' => array(),
            'orderbys' => array(),
            'conditions' => array(
                'userId = :userId',
                'type = :type',
                'status = :status',
                'transactionNo = :transactionNo',
            ),
        );
    }
}
