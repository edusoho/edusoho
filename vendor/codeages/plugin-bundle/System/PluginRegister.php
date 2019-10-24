<?php

namespace Codeages\PluginBundle\System;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class PluginRegister
{
    protected $pluginRootDir;

    protected $biz;

    public function __construct($rootDir, $pluginBaseDir, $biz = null)
    {
        $this->rootDir = rtrim($rootDir, "\/");
        $this->pluginRootDir = $this->rootDir."/{$pluginBaseDir}";
        $this->biz = $biz;
    }

    public function isPluginRegisted($code)
    {
        $app = $this->biz->service('CodeagesPluginBundle:AppService')->getAppByCode($code);

        return $app ? true : false;
    }

    public function parseMetas($code)
    {
        $file = $this->getPluginMetasFile($code);
        if (!file_exists($file)) {
            throw new \RuntimeException('Plugin metas file (plugin.json) not exist');
        }

        $metas = json_decode(file_get_contents($file), true);
        if (empty($metas)) {
            throw new \RuntimeException('Parse plugin metas file failed.');
        }

        if (empty($metas['code']) || empty($metas['name']) || empty($metas['version']) || empty($metas['support_version'])) {
            throw new \RuntimeException('Plugin metas file parameters missing, must have `code`, `name`, `version`, `support_version`.');
        }

        if ($code !== $metas['code']) {
            throw new \RuntimeException("Plugin meta code must equal `{$code}`, but you give `{$metas['code']}`");
        }

        return $metas;
    }

    public function executeDatabaseScript($code)
    {
        $file = $this->getPluginDirectory($code).DIRECTORY_SEPARATOR.'Scripts'.DIRECTORY_SEPARATOR.'database.sql';
        if (!file_exists($file)) {
            return false;
        }

        $this->biz['db']->query(file_get_contents($file));

        return true;
    }

    public function executeScript($code)
    {
        $file = $this->getPluginDirectory($code).DIRECTORY_SEPARATOR.'Scripts'.DIRECTORY_SEPARATOR.'InstallScript.php';
        if (!file_exists($file)) {
            return false;
        }

        include $file;
        if (!class_exists('InstallScript')) {
            throw new \RuntimeException("InstallScript class not found in {$file}.");
        }

        $installer = new \InstallScript($this->biz);
        $installer->setInstallMode('command');
        $installer->execute();

        return true;
    }

    public function installAssets($code)
    {
        $php = escapeshellarg($this->getPhp(false));

        $phpArgs = implode(' ', array_map('escapeshellarg', $this->getPhpArguments()));

        $consoleDir = dirname($this->pluginRootDir).'/app';
        $console = escapeshellarg($consoleDir.'/console');
        $cmd = 'assets:install --symlink --relative web';

        $process = new Process($php.($phpArgs ? ' '.$phpArgs : '').' '.$console.' '.$cmd);
        $process->mustRun();

        return $process->getOutput();
    }

    public function registerPlugin($code)
    {
        $plugin = $this->parseMetas($code);

        return $this->biz->service('CodeagesPluginBundle:AppService')->registerPlugin($plugin);
    }

    public function refreshInstalledPluginConfiguration()
    {
        $plugins = $this->biz->service('CodeagesPluginBundle:AppService')->findAllPlugins();
        $installeds = array();
        foreach ($plugins as $plugin) {
            if ($plugin['protocol'] < 3) {
                continue;
            }
            $installeds[$plugin['code']] = array(
                'code' => $plugin['code'],
                'version' => $plugin['version'],
                'type' => $plugin['type'],
                'protocol' => $plugin['protocol'],
            );
        }

        $manager = new PluginConfigurationManager(dirname($this->pluginRootDir).'/app');
        $manager->setInstalledPlugins($installeds)->save();

        $this->refreshInstalledPluginRouting($installeds);
    }

    protected function refreshInstalledPluginRouting($plugins)
    {
        $fs = new Filesystem();
        $routing = array();

        foreach ($plugins as $plugin) {
            foreach (array('' => 'routing.yml', 'admin' => 'routing_admin.yml', 'admin/v2' => 'routing_admin_v2.yml') as $prefix => $filename) {
                if ($plugin['protocol'] < 3) {
                    continue;
                }

                $resourcePath = sprintf('%sPlugin/Resources/config/%s', ucfirst($plugin['code']), $filename);
                $filePath = sprintf('%s/%s', $this->pluginRootDir, $resourcePath);

                if ($fs->exists($filePath)) {
                    $name = str_replace('/', '_', $prefix);
                    $routing["_plugin_{$plugin['code']}_{$name}"] = array(
                        'resource' => '@'.$resourcePath,
                        'prefix' => '/'.$prefix,
                    );
                }
            }
        }

        $routingFile = $this->rootDir.'/app/config/routing_plugins.yml';

        if (!$fs->exists($routingFile)) {
            $fs->touch($routingFile);
        }

        file_put_contents($routingFile, Yaml::dump($routing));
    }

    public function removePlugin($code)
    {
        $this->biz->service('CodeagesPluginBundle:AppService')->removePlugin($code);
    }

    protected function getPhp($includeArgs = true)
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find($includeArgs)) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }

    protected function getPhpArguments()
    {
        $arguments = array();

        $phpFinder = new PhpExecutableFinder();
        if (method_exists($phpFinder, 'findArguments')) {
            $arguments = $phpFinder->findArguments();
        }

        if (false !== $ini = php_ini_loaded_file()) {
            $arguments[] = '--php-ini='.$ini;
        }

        return $arguments;
    }

    public function getPluginDirectory($code)
    {
        return $this->pluginRootDir.DIRECTORY_SEPARATOR.ucfirst($code).'Plugin';
    }

    public function getPluginMetasFile($code)
    {
        return $this->getPluginDirectory($code).DIRECTORY_SEPARATOR.'plugin.json';
    }
}
