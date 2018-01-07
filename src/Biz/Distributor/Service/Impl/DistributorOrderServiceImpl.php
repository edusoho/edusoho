<?php

namespace Biz\Distributor\Service\Impl;

use QiQiuYun\SDK\Auth;

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

    private function sign($arr, $time, $once)
    {
        ksort($arr);
        $json = implode('\n', array($time, $once, json_encode($arr)));
        $settings = $this->getSettingService()->get('storage', array());
        $auth = new Auth($settings['cloud_access_key'], $settings['cloud_secret_key']);

        return $auth->sign($json);
    }
}
