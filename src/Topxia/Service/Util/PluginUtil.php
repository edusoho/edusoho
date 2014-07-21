<?php

namespace Topxia\Service\Util;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;

use Topxia\Service\Common\ServiceKernel;

class PluginUtil
{
	private static $filesystem;
	private static $kernel;

	public static function refresh ()
	{
        self::$filesystem = new Filesystem();
        self::$kernel = ServiceKernel::instance();

        $count = self::getAppService()->findAppCount();
        $apps = self::getAppService()->findApps(0, $count);

        self::refreshMetaFile($apps);
        self::refreshRoutingFile($apps);
	}

	public static function refreshMetaFile($apps)
    {
        $pluginMetas = array(
            'protocol' => '1.0',
            'installed' => array()
        );

        $cop = self::getSettingService()->get('_app_cop', null);

        foreach ($apps as $app) {
            if ($app['code'] == 'MAIN' or $cop) {
                continue;
            }

            $pluginMetas['installed'][] = $app['code'];
        }

        $dataDirectory = realpath(self::$kernel->getParameter('kernel.root_dir') . '/data/');
        if (empty($dataDirectory)) {
            throw new \RuntimeException('app/data目录不存在，请先创建');
        }

        $metaFilePath = $dataDirectory . '/plugin_installed.php';
        if (self::$filesystem->exists($metaFilePath)) {
            self::$filesystem->remove($metaFilePath);
        }

        $fileContent = "<?php \nreturn " . var_export($pluginMetas, true) . ";";
        file_put_contents($metaFilePath, $fileContent);
    }

    public static function refreshRoutingFile($apps)
    {
        $pluginRootDirectory = realpath(self::$kernel->getParameter('kernel.root_dir') . '/../plugins');

        $config = '';

        $cop = self::getSettingService()->get('_app_cop', null);

        foreach ($apps as $app) {
            if ($app['code'] == 'MAIN' or $cop) {
                continue;
            }
            $code = $app['code'];

            $routingPath = sprintf("{$pluginRootDirectory}/%s/%sBundle/Resources/config/routing.yml", ucfirst($code), ucfirst($code));
            if (self::$filesystem->exists($routingPath)) {
                $config .= "_plugin_{$code}_web:\n";
                $config .= sprintf("    resource: \"@%sBundle/Resources/config/routing.yml\"\n", ucfirst($code));
                $config .= "    prefix:   /\n";
            }

            $routingPath = sprintf("{$pluginRootDirectory}/%s/%sBundle/Resources/config/routing_admin.yml", ucfirst($code), ucfirst($code));
            if (self::$filesystem->exists($routingPath)) {
                $config .= "_plugin_{$code}_admin:\n";
                $config .= sprintf("    resource: \"@%sBundle/Resources/config/routing_admin.yml\"\n", ucfirst($code));
                $config .= "    prefix:   /admin\n";
            }
        }

        $pluginRouteFilePath = self::$kernel->getParameter('kernel.root_dir') . '/config/routing_plugins.yml';
        if (!self::$filesystem->exists($pluginRouteFilePath)) {
            self::$filesystem->touch($pluginRouteFilePath);
        }

        file_put_contents($pluginRouteFilePath, $config);

    }

    private static function getSettingService()
    {
        return self::$kernel->createService('System.SettingService');
    }

	private static function getAppService()
	{
		return self::$kernel->createService('CloudPlatform.AppService');
	}

}