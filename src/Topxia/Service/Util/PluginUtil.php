<?php

namespace Topxia\Service\Util;

use Codeages\PluginBundle\System\PluginConfigurationManager;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class PluginUtil extends BaseService
{
    private static $filesystem;
    private static $kernel;

    public static function refresh()
    {
        self::$filesystem = new Filesystem();
        self::$kernel     = ServiceKernel::instance();

        $count = self::getAppService()->findAppCount();
        $apps  = self::getAppService()->findApps(0, $count);

        self::refreshMetaFile($apps);
        self::refreshRoutingFile($apps);
    }

    public static function refreshMetaFile($apps)
    {
        $pluginMetas = array(
            'protocol'  => '2.0',
            'installed' => array()
        );

        foreach ($apps as $app) {
            if ($app['code'] == 'MAIN') {
                continue;
            }

            $pluginMetas['installed'][$app['code']] = array(
                'code'    => $app['code'],
                'version' => $app['version'],
                'type'    => $app['type'],
                'protocol' => empty($app['protocol']) ? 2 : $app['protocol'],
            );
        }

        $manager = new PluginConfigurationManager(self::$kernel->getParameter('kernel.root_dir'));
        $manager->setInstalledPlugins($pluginMetas['installed'])->save();
    }

    public static function refreshRoutingFile($apps)
    {
        $pluginRootDirectory = realpath(self::$kernel->getParameter('kernel.root_dir').'/../plugins');

        $config = '';

        foreach ($apps as $app) {
            if ($app['code'] == 'MAIN') {
                continue;
            }
            $code = $app['code'];

            if ($app['protocol'] == 2) {
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
            } else {
                $routingPath = sprintf("{$pluginRootDirectory}/%sPlugin/Resources/config/routing.yml", ucfirst($code));
                if (self::$filesystem->exists($routingPath)) {
                    $config .= "_plugin_{$code}_web:\n";
                    $config .= sprintf("    resource: \"@%sPlugin/Resources/config/routing.yml\"\n", ucfirst($code));
                    $config .= "    prefix:   /\n";
                }

                $routingPath = sprintf("{$pluginRootDirectory}/%sPlugin/Resources/config/routing_admin.yml", ucfirst($code), ucfirst($code));
                if (self::$filesystem->exists($routingPath)) {
                    $config .= "_plugin_{$code}_admin:\n";
                    $config .= sprintf("    resource: \"@%sPlugin/Resources/config/routing_admin.yml\"\n", ucfirst($code));
                    $config .= "    prefix:   /admin\n";
                }
            }
        }

        $pluginRouteFilePath = self::$kernel->getParameter('kernel.root_dir').'/config/routing_plugins.yml';
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
