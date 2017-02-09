<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;

class PluginToolkit
{
    public static function isPluginInstalled($code)
    {
        $plugins = self::getPlugins();
        if (isset($plugins[$code])) {
            return true;
        }
        return false;
    }

    public static function getPluginVersion($code)
    {
        $plugins = self::getPlugins();
        if (isset($plugins[$code])) {
            return $plugins[$code]["version"];
        }
        return null;
    }

    public static function getPlugins()
    {
        $dir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/plugin_installed.php';
        if (file_exists($dir)) {
            $plugins = include $dir;
            return $plugins["installed"];
        }
        return array();
    }
}
