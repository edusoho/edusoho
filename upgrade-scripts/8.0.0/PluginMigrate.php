<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;


class PluginMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->exec(
            "
            delete from cloud_app where code = 'Homework';
            "
        );

        $pluginFile = $this->getPluginConfig();
        $config = require_once $pluginFile;
        if (!empty($config['installed_plugins']['Homework'])) {
        	unset($config['installed_plugins']['Homework']);
        }

        $content = "<?php \n return " . var_export($config, true) . ";";
        $saved = file_put_contents($pluginFile, $content);

        $filesystem = new Filesystem();

        if ($this->filesystem->exists($this->buildDirectory)) {
            $this->filesystem->remove($this->buildDirectory);
        }
    }


    protected function getPluginConfig()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/config/plugin.php';
    }

    protected function moveRoutingPluginsYml()
    {
        $file = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/config/routing_plugin.yml';
        $targetFile = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/config/old_routing_plugin.yml';
        $filesystem = new Filesystem();

        if ($this->filesystem->exists($file)) {
            $this->filesystem->copy($file, $targetFile, true);
            $this->filesystem->touch($file);
        }
    }
}
