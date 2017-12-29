<?php

namespace Biz\Distributor\Service\Impl;

use Biz\BaseService;
use Biz\Distributor\Service\DistributorService;

abstract class BaseDistributorServiceImpl extends BaseService implements DistributorService
{
    public function findJobData()
    {
        $conditions = array(
            'jobType' => $this->getJobType(),
            'statusArr' => array('pending', 'error'),
        );

        return $this->getDistributorJobDataDao()->search(
            $conditions,
            array('id', 'asc'),
            0,
            100
        );
    }

    public function createJobData($data)
    {
        $result = array(
            'data' => json_encode($this->convertData($data)),
            'jobType' => $this->getJobType(),
            'status' => $this->getDefaultStatus(),
            'errMsg' => '',
        );
        $this->getDistributorJobDataDao()->create($result);
    }

    abstract protected function convertData($data);

    abstract protected function getJobType();

    protected function getDefaultStatus()
    {
        return 'pending';
    }

    protected function getDistributorJobDataDao()
    {
        return $this->createDao('Distributor:DistributorJobDataDao');
    }
}
