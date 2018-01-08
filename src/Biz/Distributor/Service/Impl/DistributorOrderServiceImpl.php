<?php

namespace Biz\Distributor\Service\Impl;

use QiQiuYun\SDK\Auth;
use Biz\Distributor\Util\DistributorJobStatus;

class DistributorOrderServiceImpl extends BaseDistributorServiceImpl
{
    public function getPostMethod()
    {
        return 'postOrders';
    }

    protected function convertData($order)
    {
        return array(
        );
    }

    protected function getJobType()
    {
        return 'Order';
    }

    protected function getNextJobType()
    {
        return 'User';
    }

    protected function getDependentTarget($order)
    {
        $userJobData = $this->getDistributorJobDataDao()->search(
            array('status' => DistributorJobStatus::$finished, 'target' => 'user:'.$order['userId']),
            array('id' => 'DESC'),
            0,
            1
        );

        if (empty($userJobData)) {
            return '';
        } else {
            return 'user:'.$order['userId'];
        }
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
