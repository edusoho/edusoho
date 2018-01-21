<?php

namespace Biz\Distributor\Service\Impl;

use Biz\Distributor\Service\DistributorService;
use Biz\Distributor\Util\DistributorJobStatus;
use Biz\Marketing\Service\Impl\MarketingServiceImpl;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

abstract class BaseDistributorServiceImpl extends MarketingServiceImpl implements DistributorService
{
    public function findJobData()
    {
        $conditions = array(
            'jobType' => $this->getJobType(),
            'statusArr' => DistributorJobStatus::getSendableStatus(),
        );

        return $this->getDistributorJobDataDao()->search($conditions, array(), 0, 100);
    }

    public function createJobData($dataObj)
    {
        $result = array(
            'data' => $this->convertData($dataObj),
            'jobType' => $this->getJobType(),
            'status' => DistributorJobStatus::$PENDING,
        );
        $this->getDistributorJobDataDao()->create($result);
    }

    /**
     * @param status 见 DistributorJobStatus.php 如 DistributorJobStatus::$PENDING
     */
    public function batchUpdateStatus($jobData, $status)
    {
        $helper = new BatchUpdateHelper($this->getDistributorJobDataDao());
        foreach ($jobData as $single) {
            $helper->add('id', $single['id'], array('status' => $status));
        }
        $helper->flush();
    }

    protected function getServerUrlConfig()
    {
        return array(
            'defaultUrl' => 'http://fx.marketing.com',
            'developerSettingName' => 'distributor_server',
        );
    }

    /**
     * 定时任务用， 发送给 营销平台的 type, 订单为 order, 用户 为 student
     */
    abstract public function getSendType();

    abstract protected function convertData($data);

    abstract protected function getJobType();

    protected function getDistributorJobDataDao()
    {
        return $this->createDao('Distributor:DistributorJobDataDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
