<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobPoolDao;

class JobPoolDaoImpl extends GeneralDaoImpl implements JobPoolDao
{
    protected $table = 'biz_scheduler_job_pool';

    public function getByName($name = 'default')
    {
        return $this->getByFields(array('name' => $name));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
        );
    }
}
