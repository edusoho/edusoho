<?php

namespace Topxia\Service\Util;

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

    private static function getAppService()
    {
        return self::$kernel->createService('CloudPlatform.AppService');
    }
}

//@TODO version 7.3.4 之后需删除，引用codeags里的
class PluginConfigurationManager
{
    protected $filepath;

    protected $config;

    protected $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = rtrim($rootDir, "\/");
        $this->filepath = $this->rootDir . '/config/plugin.php';
        if (!file_exists($this->filepath)) {
            $this->config = array();
        } else {
            $this->config = require $this->filepath;
        }

    }

    public function getActiveThemeName()
    {
        return empty($this->config['active_theme_name']) ? null : $this->config['active_theme_name'];
    }

    public function getActiveThemeDirectory()
    {
        $name = $this->getActiveThemeName();
        if (empty($name)) {
            return null;
        }

        return sprintf('%s/web/themes/%s', dirname($this->rootDir), $name);
    }

    public function setActiveThemeName($name)
    {
        $this->config['active_theme_name'] = $name;
        return $this;
    }

    public function getInstalledPlugins()
    {
        return empty($this->config['installed_plugins']) ? array() : $this->config['installed_plugins'];
    }

    public function setInstalledPlugins($plugins)
    {
        $this->config['installed_plugins'] = $plugins;
        return $this;
    }

    public function getInstalledPluginBundles()
    {
        $bundlues = array();
        $plugins = $this->getInstalledPlugins();

        foreach ($plugins as $plugin) {
            if ($plugin['type'] != 'plugin') {
                continue;
            }

            $code = ucfirst($plugin['code']);
            if ($plugin['protocol'] == 2) {
                $class = "{$code}\\{$code}Bundle\\{$code}Bundle";
            } else {
                $class = "{$code}Plugin\\{$code}Plugin";
            }

            $bundlues[] = new $class();
        }

        return $bundlues;
    }

    public function save()
    {
        $content = "<?php \n return " . var_export($this->config, true) . ";";
        $saved = file_put_contents($this->filepath, $content);

        if ($saved === false) {
            throw new \RuntimeException("Save plugin configuration ({$this->filepath}) failed, may be this file is not writeable.");
        }

        return $this;
    }

}
