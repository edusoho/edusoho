<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;


class PluginMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->exec("delete from cloud_app where code in ('Homework', 'Crm');");

        $theme = $this->getSettingService()->get('theme');

        if(empty($theme['uri'])){
            $this->getSettingService()->set('theme', array('uri' => 'jianmo'));
        }else if(!in_array($theme['uri'], array('jianmo', 'autumn', 'default', 'default-b'))){
            $this->getSettingService()->set('theme', array('uri' => 'jianmo'));
        }

        $theme = $this->getSettingService()->get('theme');

        $pluginFile = $this->getPluginConfig();
        $pluginFile = realpath($pluginFile);
        if (!empty($pluginFile)) {
            $config = require_once $pluginFile;

            foreach (array('Homework', 'Crm') as $key => $value) {
                if (isset($config['installed_plugins'][$value])) {
                    $installedPlugins = $config['installed_plugins'];
                    unset($installedPlugins[$value]);
                    $config['installed_plugins'] = $installedPlugins;
                }
            }

            $config = is_array($config) ? $config : array();
            $config['active_theme_name'] = $theme['uri'];

            $content = "<?php \n return " . var_export($config, true) . ";";
            $saved = file_put_contents($pluginFile, $content);
        }

        $this->moveRoutingPluginsYml();

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $currentTime = time();
        $this->getSettingService()->set('crontab_next_executed_time', $currentTime);
        $this->getCrontabService()->setNextExcutedTime($currentTime);
    }

    protected function getSettingService()
    {
        try {
            return ServiceKernel::instance()->createService('System.SettingService');
        } catch (\Exception $e) {
            return ServiceKernel::instance()->createService('System:SettingService');
        }
    }

    protected function getCrontabService()
    {
        try {
            return ServiceKernel::instance()->createService('Crontab.CrontabService');
        } catch (\Exception $e) {
            return ServiceKernel::instance()->createService('Crontab:CrontabService');
        }
    }

    protected function getPluginConfig()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/config/plugin.php';
    }

    protected function moveRoutingPluginsYml()
    {
        $file = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/config/routing_plugins.yml';
        $targetFile = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/config/old_routing_plugins.yml';
        $filesystem = new Filesystem();

        if ($filesystem->exists($file)) {
            $filesystem->copy($file, $targetFile, true);
            $filesystem->remove($file);
            $filesystem->touch($file);
        }
    }
}
