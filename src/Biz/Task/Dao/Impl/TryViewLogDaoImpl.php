<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TryViewLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TryViewLogDaoImpl extends GeneralDaoImpl implements TryViewLogDao
{
    protected $table = 'course_task_try_view';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array(),
            'conditions' => array()
        );
    }
}