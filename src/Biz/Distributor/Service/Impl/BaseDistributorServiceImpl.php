<?php

namespace Biz\Distributor\Service\Impl;

use Biz\Distributor\Service\DistributorService;
use Biz\Distributor\Util\DistributorJobStatus;
use Biz\Marketing\Service\Impl\MarketingCourseServiceImpl;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;

abstract class BaseDistributorServiceImpl extends MarketingCourseServiceImpl implements DistributorService
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
            'status' => DistributorJobStatus::PENDING,
        );
        $this->getDistributorJobDataDao()->create($result);
    }

    /**
     * @param status 见 DistributorJobStatus.php 如 DistributorJobStatus::PENDING
     */
    public function batchUpdateStatus($jobData, $status)
    {
        $helper = new BatchUpdateHelper($this->getDistributorJobDataDao());
        foreach ($jobData as $single) {
            $helper->add('id', $single['id'], array('status' => $status));
        }
        $helper->flush();
    }

    public function batchCreateJobData($jobData)
    {
        $helper = new BatchCreateHelper($this->getDistributorJobDataDao());
        foreach ($jobData as $single) {
            $result = array(
                'data' => $this->convertData($single),
                'jobType' => $this->getJobType(),
                'status' => DistributorJobStatus::PENDING,
            );
            $helper->add($result);
        }
        $helper->flush();
    }

    public function getDrpService()
    {
        return $this->biz['qiQiuYunSdk.drp'];
    }

    /**
     * 定时任务用， 发送给 营销平台的 type, 订单为 order, 用户 为 user
     */
    abstract public function getSendType();

    /**
     * 保存数据时，转化数据用，转为 distributor_job_data 内的data属性
     */
    abstract protected function convertData($data);

    /**
     * distributor_job_data的type，及 相应的DistributorService的关键字，
     *   如 值为User, 则相应的service为 DistributorUserService
     */
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
