<?php

namespace Biz\BehaviorVerification\Dao\Impl;

use Biz\BehaviorVerification\Dao\SmsRequestLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SmsRequestLogDaoImpl extends GeneralDaoImpl implements SmsRequestLogDao
{
    protected $table = 'sms_request_log';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array('id', 'created_time', 'updated_time'),
            'conditions' => [
                'mobile = :mobile',
                'mobile IN (:mobiles)',
                'ip => :ip',
                'ip => IN (:ips)',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
            ],
        );
    }
}