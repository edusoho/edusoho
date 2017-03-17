<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Yaml\Yaml;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

//        try {
            $this->fixCustomRouting();
            $this->fixRoutingYml();
//        } catch (\Exception $e) {
//        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
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
            $fileSystem->remove($originAdminRoutingYml);
        }

        $originWebRoutingYml = $customDir."/WebBundle/Resources/config/routing.yml";
        $webRoutingYml = $customDir."/WebBundle/Resources/config/custom_routing.yml";
        if ($fileSystem->exists($originWebRoutingYml)) {
            if (!$fileSystem->exists($webRoutingYml)) {
                $fileSystem->copy($originWebRoutingYml, $webRoutingYml);
            }
            $fileSystem->remove($originWebRoutingYml);
        }
    }

    private function fixRoutingYml()
    {
        $routingYml = ServiceKernel::instance()->getParameter('kernel.root_dir')."/config/routing.yml";
        if (file_exists($routingYml)) {
            $contents = Yaml::parse($routingYml);
            if (!empty($contents['custom_web']['resource'])) {
                if ($contents['custom_web']['resource'] == "@CustomWebBundle/Resources/config/routing.yml") {
                    $contents['custom_web']['resource'] = "@CustomWebBundle/Resources/config/custom_routing.yml";
                }

                if ($contents['custom_admin']['resource'] == "@CustomAdminBundle/Resources/config/admin_routing.yml") {
                    $contents['custom_admin']['resource'] = "@CustomAdminBundle/Resources/config/custom_admin_routing.yml";
                }

                file_put_contents($routingYml, Yaml::dump($contents, 100));
            }
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
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
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }



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
