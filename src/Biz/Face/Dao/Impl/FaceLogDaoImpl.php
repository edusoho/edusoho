<?php

namespace Biz\Face\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Face\Dao\FaceLogDao;

class FaceLogDaoImpl extends GeneralDaoImpl implements FaceLogDao
{
    protected $table = 'face_log';

    public function declares()
    {
        return array(
            'conditions' => array(
                'id IN ( :ids )',
                'id = :id',
                'userId = :userId',
                'status = :status',
                'id NOT IN (:excludeIds)',
                'createdTime >= :createdTime_GT',
                'createdTime <= :createdTime_LE',
            ),
            'orderbys' => array(
                'createdTime',
            ),
            'timestamps' => array(
                'createdTime',
            ),
        );
    }
}
