<?php

namespace ApiBundle\Api\Util;

use Biz\System\Service\SettingService;
use Topxia\Service\Common\ServiceKernel;

class Money
{
    /**
     * @param $timestamp
     * @param string $format
     */
    public static function convert($price)
    {
        $setting = self::getSettingService()->get('coin');

        $default = array(
            'coin_enabled' => 0,
            'cash_model' => 'none',
            'cash_rate' => 1,
            'coin_name' => '虚拟币',
        );

        $setting = array_merge($default, $setting);

        $money = array(
            'currency' => 'RMB',
            'amount' => $price,
        );

        if ($setting['cash_model'] == 'currency') {
            $money['currency'] = 'coin';
        }

        if ($setting['cash_model'] != 'none') {
            $money['coinAmount'] = strval(round(floatval($price) * floatval($setting['cash_rate']), 2));
            $money['coinName'] = $setting['coin_name'];
        }

        return $money;
    }

    /**
     * @return SettingService
     */
    private static function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
