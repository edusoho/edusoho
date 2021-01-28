<?php

namespace Biz\Plumber\Dao\Impl;

use Biz\Plumber\Dao\PlumberQueueDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PlumberQueueDaoImpl extends GeneralDaoImpl implements PlumberQueueDao
{
    protected $table = 'plumber_queue';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'serializes' => [
                'body' => 'json',
                'trace' => 'json',
            ],
            'orderbys' => ['createdTime', 'id'],
            'conditions' => [
                'worker = :worker',
                'jobId = :jobId',
            ],
        ];
    }
}
