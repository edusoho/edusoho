<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;

class PluginToolkit
{
	public static function isPluginInstalled($code)
	{
		$plugins = self::getPlugins();
		if(isset($plugins["installed"][$code])){
			return true;
		}
		return false;
	}

	public static function getPluginVersion($code)
	{
		$plugins = self::getPlugins();
		if(isset($plugins["installed"][$code])){
			return $plugins["installed"][$code]["version"];
		}
		return null;
	}

	protected static function getPlugins()
	{
		$dir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/plugin_installed.php';
		if (file_exists($dir)) {
			$plugins = include $dir;
			return $plugins;
		}
		return array();
	}
}