<?php

namespace Codeages\Biz\Framework\Queue\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Queue\Dao\JobDao;

class FailedJobDaoImpl extends GeneralDaoImpl implements JobDao
{
    protected $table = 'biz_queue_failed_job';

    public function declares()
    {
        return array(
            'timestamps' => array('failed_time'),
            'serializes' => array('body' => 'php'),
            'orderbys' => array('failed_time'),
            'conditions' => array(
            ),
        );
    }
}
