<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        try {
            $this->fixCustomRouting();
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function fixCustomRouting()
    {
        $fileSystem = new Filesystem();
        $customDir = ServiceKernel::instance()->getParameter('kernel.root_dir')."/../src/Custom";

        $originAdminRoutingYml = $customDir."/AdminBundle/Resources/config/admin_routing.yml";
        $adminRoutingYml = $customDir."/AdminBundle/Resources/config/custom_admin_routing.yml";
        if ($fileSystem->exists($originAdminRoutingYml)) {
            if (!$fileSystem->exists($adminRoutingYml)) {
                $fileSystem->copy($originAdminRoutingYml, $adminRoutingYml);
            }
        }

        $originWebRoutingYml = $customDir."/WebBundle/Resources/config/routing.yml";
        $webRoutingYml = $customDir."/WebBundle/Resources/config/custom_routing.yml";
        if ($fileSystem->exists($originWebRoutingYml)) {
            if (!$fileSystem->exists($webRoutingYml)) {
                $fileSystem->copy($originWebRoutingYml, $webRoutingYml);
            }
        }
    }
     protected function getSettingService()
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
