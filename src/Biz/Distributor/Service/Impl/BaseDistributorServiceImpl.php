<?php

namespace Biz\Distributor\Service\Impl;

use Biz\BaseService;
use Biz\Distributor\Service\DistributorService;
use QiQiuYun\SDK\Service\DrpService;
use QiQiuYun\SDK\Auth;

abstract class BaseDistributorServiceImpl extends BaseService implements DistributorService
{
    public function findJobData()
    {
        $conditions = array(
            'jobType' => $this->getJobType(),
            'statusArr' => array('pending', 'error'),
        );

        return $this->getDistributorJobDataDao()->search($conditions, array(), 0, 100);
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

    abstract protected function convertData($data);

    abstract protected function getJobType();

    abstract protected function getNextJobType();

    protected function getDefaultStatus()
    {
        return 'pending';
    }

    protected function getDistributorJobDataDao()
    {
        return $this->createDao('Distributor:DistributorJobDataDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
