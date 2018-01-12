<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobLogDao;

class JobLogDaoImpl extends GeneralDaoImpl implements JobLogDao
{
    protected $table = 'biz_scheduler_job_log';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'serializes' => array(
                'args' => 'json',
            ),
            'orderbys' => array('created_time', 'id'),
            'conditions' => array(
                'job_fired_id = :job_fired_id ',
            ),
        );
    }
}
