<?php

namespace Biz\InfoSecurity\Dao\Impl;

use Biz\InfoSecurity\Dao\MobileAccessLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MobileAccessLogDaoImpl extends GeneralDaoImpl implements MobileAccessLogDao
{
    protected $table = 'mobile_access_log';

    public function declares()
    {
        return [
            'serializes' => [],
            'orderbys' => [],
            'timestamps' => ['createdTime'],
            'conditions' => []
        ];
    }
}
