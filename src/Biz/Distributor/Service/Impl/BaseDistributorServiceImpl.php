<?php

namespace Biz\Distributor\Service\Impl;

use Biz\Distributor\Service\DistributorService;
use Biz\Distributor\Util\DistributorJobStatus;
use Biz\Marketing\Service\Impl\MarketingCourseServiceImpl;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use QiQiuYun\SDK\Auth;
use AppBundle\Common\TimeMachine;

abstract class BaseDistributorServiceImpl extends MarketingCourseServiceImpl implements DistributorService
{
    /**
     * 分销平台的token编码方式, MockController 才使用
     *   注意，$data 内的参数值必须为字符串
     *
     * @param $data, key 会被ksort重排序
     * array(
     *   'merchant_id' => '123',
     *   'agency_id' => '222',
     *   'coupon_price' => '222',  // 单位为分
     *   'coupon_expiry_day' => '12', //单位为天
     * )
     * @param $tokenExpireDateNum unix_time, 如果填了，则使用填写的时间，不填，则使用当前时间
     *
     * @return {merchant_id}:{agency_id}:{coupon_price}:{coupon_expiry_day}:{time}:{nonce}:{sign}
     *                                                                                            sign 为 添加 secretKey 后的加密方法
     */
    public function encodeToken($data, $tokenExpireDateNum = null)
    {
        $sortedData = $data;
        ksort($sortedData);
        if (empty($tokenExpireDateNum)) {
            $time = TimeMachine::time().'';
        } else {
            $time = strtotime('-1 day', $tokenExpireDateNum);
        }

        $once = md5(TimeMachine::time());

        $resultStr = '';
        foreach ($data as $key => $value) {
            if (!empty($resultStr)) {
                $resultStr .= ':';
            }

            $resultStr .= $value;
        }

        $resultStr .= ":{$time}:{$once}:{$this->sign($once, $time, $sortedData)}";

        return $resultStr;
    }

    private function sign($once, $time, $arr)
    {
        ksort($arr);
        $json = implode("\n", array($once, $time, json_encode($arr)));

        $settings = $this->getSettingService()->get('storage', array());
        $auth = new Auth($settings['cloud_access_key'], $settings['cloud_secret_key']);

        return $auth->makeSignature($json);
    }

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
        if (!empty($jobData)) {
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
    }

    public function getDrpService()
    {
        return $this->biz['qiQiuYunSdk.drp'];
    }

    /**
     * 定时任务用， 发送给 营销平台的 type, 订单为 order, 用户 为 user
     *
     * @param $data distributor_job_data 内的data属性
     */
    abstract public function getSendType($data);

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
