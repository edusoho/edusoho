<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();

            $this->updateCrontabSetting();
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

    private function updateScheme()
    {
        $connection = $this->getConnection();

        $api  = CloudAPIFactory::create('root');
        $info = $api->get('/me');

        if ($info['copyright']) {
            $copyright = $this->getSettingService()->get('copyright', array());

            $copyright['owned']          = 1;
            $copyright['thirdCopyright'] = $info['thirdCopyright'];
            $copyright['licenseDomains'] = $info['licenseDomains'];
            $this->getSettingService()->set('copyright', $copyright);
        }

        if (!$this->isTableExist('file_used')) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS  `file_used` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `type` varchar(32) NOT NULL,
                  `fileId` int(11) NOT NULL COMMENT 'upload_files id',
                  `targetType` varchar(32) NOT NULL,
                  `targetId` int(11) NOT NULL,
                  `createdTime` int(11) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `file_used_type_targetType_targetId_index` (`type`,`targetType`,`targetId`),
                  KEY `file_used_type_targetType_targetId_fileId_index` (`type`,`targetType`,`targetId`,`fileId`),
                  KEY `file_used_fileId_index` (`fileId`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
        }

        if (!$this->isFieldExist('upload_files', 'useType')) {
            $connection->exec("ALTER TABLE upload_files  ADD `useType` varchar(64) DEFAULT NULL COMMENT '文件使用的模块类型'  AFTER  `targetType`;");
        }

        if ($this->isFieldExist('cash_orders', 'payment')) {
            $connection->exec("ALTER TABLE `cash_orders` CHANGE `payment` `payment` VARCHAR(32) NOT NULL DEFAULT 'none';");
        }

        if ($this->isFieldExist('cash_flow', 'payment')) {
            $connection->exec("ALTER TABLE `cash_flow` CHANGE `payment` `payment` VARCHAR(32) NULL DEFAULT ''");
        }
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
