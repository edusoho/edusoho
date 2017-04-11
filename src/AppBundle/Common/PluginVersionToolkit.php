<?php

namespace AppBundle\Common;

class PluginVersionToolkit
{
    public static function dependencyVersion($code, $currentVersion)
    {
        $pluginVersion = array(
            'Crm' => '1.0.2',
        );

        $code = ucfirst($code);

        if (isset($pluginVersion[$code])) {
            return version_compare($currentVersion, $pluginVersion[$code], '>=');
        } else {
            return true;
        }
    }
}
