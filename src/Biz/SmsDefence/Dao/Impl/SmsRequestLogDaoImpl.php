<?php

namespace Biz\SmsDefence\Dao\Impl;

use Biz\SmsDefence\Dao\SmsRequestLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SmsRequestLogDaoImpl extends GeneralDaoImpl implements SmsRequestLogDao
{
    protected $table = 'sms_request_log';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'mobile = :mobile',
                'mobile IN (:mobiles)',
                'ip = :ip',
                'ip = IN (:ips)',
                'coordinate = (:coordinate)',
                'fingerprint = (:fingerprint)',
                'createdTime >= (:createdTime_GTE)',
            ],
        ];
    }
}
