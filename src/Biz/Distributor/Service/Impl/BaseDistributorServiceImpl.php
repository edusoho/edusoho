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

    public function encodeToken($data, $tokenExpireTime = null)
    {
        if (empty($tokenExpireTime)) {
            $time = time();
        } else {
            $time = strtotime('-1 day', $tokenExpireTime);
        }

        $once = md5(time());

        $resultStr = '';
        foreach ($data as $key => $value) {
            if (!empty($resultStr)) {
                $resultStr .= ':';
            }

            $resultStr .= $value;
        }

        $resultStr .= ":{$time}:{$once}:{$this->sign($data, $time, $once)}";

        return $resultStr;
    }

    /**
     * 分销平台的token，只能使用一次，使用多次，仍然算这个分销商的拉新用户，但不会给奖励
     *
     * @return array(
     *                'couponPrice' => 123, //优惠券，奖励多少元
     *                'couponExpiryDay' => unix_time, //优惠券有效时间
     *                'registable'  => true, //是否可注册，指的是分销平台是否颁发过这个token， 如果为false，则注册的用户不算分销平台用户
     *                'rewardable' => false //是否有奖励, 这个token还能不能继续实行奖励，如果为false, 则注册的用户不会发放优惠券
     *                )
     */
    public function decodeToken($token)
    {
        $splitedStr = explode(':', $token);

        $tokenInfo = array(
            'registable' => false,
            'rewardable' => false,
        );

        try {
            $parsedInfo = $this->getDrpService()->parseToken($token);
            $tokenInfo['registable'] = true;
            $tokenExpireTime = strtotime('+1 day', intval($parsedInfo['time']));
            if ($tokenExpireTime > time()) {
                $tokenInfo['couponPrice'] = $parsedInfo['couponPrice'];
                $tokenInfo['couponExpiryDay'] = $parsedInfo['couponExpiryDay'];
                $tokenInfo['rewardable'] = true;
            }
        } catch (\Exception $e) {
            $this->biz['logger']->error('distributor sign error BaseDistributorServiceImpl::decodeToken '.$e->getMessage(), array('trace' => $e->getTraceAsString()));
        }

        return $tokenInfo;
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

    private function sign($arr, $time, $once)
    {
        ksort($arr);
        $json = implode('\n', array($time, $once, json_encode($arr)));
        $settings = $this->getSettingService()->get('storage', array());
        $auth = new Auth($settings['cloud_access_key'], $settings['cloud_secret_key']);

        return $auth->sign($json);
    }
}
