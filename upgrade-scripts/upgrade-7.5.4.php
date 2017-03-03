<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->batchUpdate();
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
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

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    protected function batchUpdate()
    {
        $cloudSearchSettings = $this->getSettingService()->get('cloud_search');

        $cloudSearchSettings['type'] = array(
            'course'     => 1,
            'teacher'    => 1,
            'thread'     => 1, 
            'article'    => 1
        );

        $this->getSettingService()->set('cloud_search', $cloudSearchSettings);
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

//抽象类
abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct ($kernel)
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