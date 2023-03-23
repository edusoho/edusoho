<?php

namespace Biz\SmsRequestLog\Dao\impl;

use Biz\SmsRequestLog\Dao\SmsRequestLogDao;
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
                'ip = :ip',
                'ip = IN (:ips)',
                'coordinate = (:coordinate)',
                'fingerprint = (:fingerprint)',
                'created_time >= (:createdTime_GTE)',
            ],
        );
    }
}