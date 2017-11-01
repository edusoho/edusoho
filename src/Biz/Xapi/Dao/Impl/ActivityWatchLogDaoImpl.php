<?php

namespace Biz\Xapi\Dao\Impl;

use Biz\Xapi\Dao\ActivityWatchLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ActivityWatchLogDaoImpl extends GeneralDaoImpl implements ActivityWatchLogDao
{
    protected $table = 'xapi_activity_watch_log';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array(
                'created_time',
            ),
            'serializes' => array(
            ),
            'conditions' => array(
                'is_push = :is_push',
            ),
        );
    }
}