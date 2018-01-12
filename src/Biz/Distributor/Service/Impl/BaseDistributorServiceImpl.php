<?php

namespace Biz\Distributor\Service\Impl;

use Biz\BaseService;
use Biz\Distributor\Service\DistributorService;
use QiQiuYun\SDK\Service\DrpService;
use QiQiuYun\SDK\Auth;
use Biz\Distributor\Util\DistributorJobStatus;

abstract class BaseDistributorServiceImpl extends BaseService implements DistributorService
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
            'errMsg' => '',
        );
        $this->getDistributorJobDataDao()->create($result);
    }

    /**
     * @param status 见 DistributorJobStatus.php 如 DistributorJobStatus::$PENDING
     */
    public function batchUpdateStatus($jobData, $status)
    {
        foreach ($jobData as $single) {
            $this->getDistributorJobDataDao()->update($single['id'], array('status' => $status));
        }
    }

    public function getDrpService()
    {
        if (empty($this->drpService)) {
            $this->drpService = null;
            $settings = $this->getSettingService()->get('storage', array());
            if (!empty($settings['cloud_access_key']) && !empty($settings['cloud_secret_key'])) {
                $auth = new Auth($settings['cloud_access_key'], $settings['cloud_secret_key']);
                $this->drpService = new DrpService($auth);
            }
        }

        return $this->drpService;
    }

    /**
     * 定时任务用， 发送给 营销平台的 type, 订单为 order, 用户 为 student
     */
    abstract public function getSendType();

    abstract public function getNextJobType();

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
