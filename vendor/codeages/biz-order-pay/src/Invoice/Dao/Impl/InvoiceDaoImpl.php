<?php

namespace Codeages\Biz\Invoice\Dao\Impl;

use Codeages\Biz\Invoice\Dao\InvoiceDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class InvoiceDaoImpl extends GeneralDaoImpl implements InvoiceDao
{
    protected $table = 'biz_invoice';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'created_time',
            ),
            'serializes' => array(
            ),
            'timestamps' => array(
                'created_time',
                'updated_time',
            ),
            'conditions' => array(
                'id = :id',
                'user_id = :userId',
                'status = :status',
                'user_id IN ( :userIds)',
                'sn = :sn',
            ),
        );
    }
}
