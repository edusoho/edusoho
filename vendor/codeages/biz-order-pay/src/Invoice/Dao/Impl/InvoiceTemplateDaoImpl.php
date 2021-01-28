<?php

namespace Codeages\Biz\Invoice\Dao\Impl;

use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Invoice\Dao\InvoiceTemplateDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class InvoiceTemplateDaoImpl extends GeneralDaoImpl implements InvoiceTemplateDao
{
    protected $table = 'biz_invoice_template';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'is_default',
                'id',
                'created_time',
            ),
            'timestamps' => array(
                'created_time',
                'updated_time',
            ),
            'conditions' => array(
                'id = :id',
                'user_id = :userId',
            ),
        );
    }

    public function getDefaultByUserId($userId)
    {
        return $this->getByFields(array('user_id' => $userId, 'is_default' => 1));
    }
}