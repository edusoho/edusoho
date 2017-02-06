<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;

class SettingToolkit
{
    public static function getSetting($name, $default = '')
    {
        $names = explode('.', $name);

        $name = array_shift($names);

        if (empty($name)) {
            return $default;
        }

        $value = self::getSettingService()->get($name, $default);

        if (!isset($value)) {
            return $default;
        }

        if (empty($names)) {
            return $value;
        }

        $result = $value;

        foreach ($names as $name) {
            if (!isset($result[$name])) {
                return $default;
            }

            $result = $result[$name];
        }

        return $result;
    }

    protected static function getSettingService()
    {
        return self::getServiceKernel()->createService('System.SettingService');
    }

    protected static function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}