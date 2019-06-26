<?php

namespace Biz\CloudData\Dao\Impl;

use Biz\CloudData\Dao\CloudDataDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CloudDataDaoImpl extends GeneralDaoImpl implements CloudDataDao
{
    protected $table = 'cloud_data';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(
                'body' => 'json',
            ),
            'conditions' => array(
                'name = :name',
                'timestamp = :timestamp',
                'createdUserId = :createdUserId',
            ),
            'orderbys' => array(
                'updatedTime',
                'createdTime',
            ),
        );
    }
}
