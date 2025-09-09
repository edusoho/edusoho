<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme();
            $this->getConnection()->commit();
            if(!empty($result)){
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir') . "../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();

        if(!$this->isFieldExist('cloud_app', 'protocol')) {
            $connection->exec("ALTER TABLE `cloud_app` ADD `protocol` TINYINT UNSIGNED NOT NULL DEFAULT '2' AFTER `type`;");
        }

        $theme = $this->getSettingService()->get('theme');

        if(isset($theme['uri'])){
            $manager = new PluginConfigurationManager(ServiceKernel::instance()->getParameter('kernel.root_dir'));
            $manager->setActiveThemeName($theme['uri'])->save();
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql    = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * @return \Topxia\Service\System\Impl\SettingServiceImpl
     */
    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return \Codeages\Biz\Framework\Dao\Connection
     */
    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}

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
