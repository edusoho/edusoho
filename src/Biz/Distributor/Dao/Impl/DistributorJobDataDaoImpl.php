<?php

namespace Biz\Distributor\Dao\Impl;

use Biz\Distributor\Dao\DistributorJobDataDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DistributorJobDataDaoImpl extends GeneralDaoImpl implements DistributorJobDataDao
{
    protected $table = 'distributor_job_data';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'status = (:statusArr)',
            ),
        );
    }
}
