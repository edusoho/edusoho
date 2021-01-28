<?php

namespace Biz\Distributor\Dao\Impl;

use Biz\Distributor\Dao\DistributorJobDataDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class DistributorJobDataDaoImpl extends AdvancedDaoImpl implements DistributorJobDataDao
{
    protected $table = 'distributor_job_data';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'jobType = :jobType',
                'status in (:statusArr)',
                'status = :status',
            ),
            'orderbys' => array(
                'id',
            ),
            'serializes' => array(
                'data' => 'json',
            ),
        );
    }
}
