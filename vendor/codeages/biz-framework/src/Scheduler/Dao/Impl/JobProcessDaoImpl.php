<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobProcessDao;

class JobProcessDaoImpl extends GeneralDaoImpl implements JobProcessDao
{
    protected $table = 'biz_scheduler_job_process';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
        );
    }
}
