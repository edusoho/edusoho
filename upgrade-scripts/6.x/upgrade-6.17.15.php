<?php

use Topxia\Common\BlockToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    const VERSION = '6.17.15';

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);
            $this->getConnection()->commit();

            $this->updateCrontabSetting();

            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());
    }

    private function updateScheme($index)
    {
        $connection = $this->getConnection();
        try{
            if (!$this->isFieldExist('course_material', 'source')) {
                $connection->exec("ALTER TABLE `course_material` ADD `source` varchar(50) NOT NULL DEFAULT 'coursematerial' AFTER `fileSize`;");
                $this->logger('INFO', 'course_material增加字段source');
            }

        } catch (\Exception $e) {
            $this->logger('ERROR', $e->getMessage());
        }
    }

    protected function logger($level, $message)
    {
        $data = date("Y-m-d H:i:s").sprintf('[%s] %s %s', $level, self::VERSION, $message.PHP_EOL);
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    protected function getLoggerFile()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/logs/upgrade.log";
    }

    private function updateCrontabSetting()
    {
        $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/data/crontab_config.yml");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
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
