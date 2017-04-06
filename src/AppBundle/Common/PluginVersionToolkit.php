<?php

namespace AppBundle\Common;

class PluginVersionToolkit
{
    public static function dependencyVersion($code, $currentVersion)
    {
        $pluginVersion = array(
            'Vip' => '1.6.6',
            'UserImporter' => '2.1.6',
            'GracefulTheme' => '1.4.23',
            'MoneyCard' => '2.0.5',
            'Discount' => '1.1.8',
            'Coupon' => '2.1.6',
            'ChargeCoin' => '1.2.6',
            'QuestionPlus' => '1.2.2',
        );

        $code = ucfirst($code);

        if (isset($pluginVersion[$code])) {
            return version_compare($currentVersion, $pluginVersion[$code], '>=');
        } else {
            return true;
        }
    }
}
