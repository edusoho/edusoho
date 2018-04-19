<?php

namespace Biz\Distributor\Service\Impl;

use Biz\Distributor\Service\DistributorProductService;
use AppBundle\Common\TimeMachine;
use QiQiuYun\SDK\Auth;

class DistributorCourseOrderServiceImpl extends DistributorOrderServiceImpl implements DistributorProductService
{
    public function getSendType()
    {
        return 'courseOrder';
    }

    public function getRoutingName()
    {
        return 'course_show';
    }

    public function getRoutingParams(Array $tokenInfo)
    {
        return array('id' => $tokenInfo['product_id']);
    }

    public function encodeToken($data)
    {
        $time = TimeMachine::time();
        $once = md5(TimeMachine::time());

        $resultStr = '';
        foreach ($data as $key => $value) {
            if (!empty($resultStr)) {
                $resultStr .= ':';
            }

            $resultStr .= $value;
        }

        $resultStr .= ":{$time}:{$once}:{$this->sign($once, $time, $data)}";

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

    //TODO 分销平台接口弄好后 再根据接口改动
    public function decodeToken($token)
    {
        try {
            $splitedStr = explode(':', $token);
            $tokenInfo = array(
                'org_id' => $splitedStr[0],
                'type' => $splitedStr[1],
                'product_id' => $splitedStr[2],
                'merchant_id' => $splitedStr[3],
                'time' => $splitedStr[4],
                'once' => $splitedStr[5],
                'sign' => $splitedStr[6],
            );
        } catch (\Exception $e) {
            $this->biz['logger']->error('distributor sign error BaseDistributorServiceImpl::decodeToken '.$e->getMessage(), array('trace' => $e->getTraceAsString()));
        }

        return $tokenInfo;
    }

    protected function convertData($order)
    {
        $result = parent::convertData($order);
    }

    protected function getJobType()
    {
        return 'CourseOrder';
    }
}
