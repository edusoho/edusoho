<?php

namespace Biz\Distributor\Service\Impl;

use Biz\BaseService;

abstract class BaseSyncServiceImpl extends BaseService
{
    public function sync()
    {
        $this->dealData();
        $this->afterDeal();
    }

    abstract public function getNextJob();

    /**
     * 调用DrpService的什么方法进行数据发送
     */
    abstract public function getPostMethod();

    protected function afterDeal()
    {
    }

    protected function getDistributorService()
    {
        return $this->createService('Distributor:DistributorService');
    }

    private function dealData()
    {
        $jobDatas = $this->getDistributorService()->searchJobData(
            array('statusArr' => array('pending', 'error')),
            array('id', 'asc'),
            0,
            100
        );
    }
}
