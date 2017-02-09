<?php

namespace Topxia\Common;


class PluginVersionToolkit
{
    public static function dependencyVersion($code, $currentVersion)
    {
        $pluginVersion = array(
            'Vip'          => '1.6.3',
            'UserImporter' => '2.1.5',
            'MoneyCard'    => '2.0.4',
            'Discount'     => '1.1.7',
            'Coupon'       => '2.1.4',
            'ChargeCoin'   => '1.2.5'
        );

        $code = ucfirst($code);

        if (isset($pluginVersion[$code])) {
            return version_compare($currentVersion, $pluginVersion[$code], '>=');
        } else {
            return true;
        }
    }
}