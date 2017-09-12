<?php

namespace Codeages\Biz\Framework\Pay\Dao\Impl;

use Codeages\Biz\Framework\Pay\Dao\PayAccountDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PayAccountDaoImpl extends GeneralDaoImpl implements PayAccountDao
{
    protected $table = 'biz_pay_account';

    public function getByUserId($userId)
    {
        return $this->getByFields(array(
            'user_id' => $userId
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time')
        );
    }
}