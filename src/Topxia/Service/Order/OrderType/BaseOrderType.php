<?php
namespace Topxia\Service\Order\OrderType;

use Topxia\Service\Common\ServiceKernel;

class BaseOrderType
{
    protected function getCoinSetting()
    {
        $coinSetting = $this->getSettingService()->get("coin");

        $coinEnable = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;

        $cashRate = 1;

        if ($coinEnable && array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $priceType = "RMB";

        if ($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
            $priceType = $coinSetting["price_type"];
        }

        return array($coinEnable, $priceType, $cashRate);
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}
