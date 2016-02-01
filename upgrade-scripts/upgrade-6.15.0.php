<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

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

        if (!$this->isTableExist('keyword')) {
            $connection->exec("
                CREATE TABLE `keyword` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `name` varchar(64) CHARACTER SET utf8 NOT NULL,
                  `state` ENUM('replaced','banned') NOT NULL DEFAULT 'replaced',
                  `bannedNum` int(10) unsigned NOT NULL DEFAULT '0',
                  `createdTime` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `name` (`name`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('keyword_banlog')) {
            $connection->exec("
                CREATE TABLE `keyword_banlog` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `keywordId` int(10) unsigned NOT NULL,
                  `keywordName` varchar(64) NOT NULL DEFAULT '',
                  `state` ENUM('replaced','banned') NOT NULL DEFAULT 'replaced',
                  `text` text NOT NULL,
                  `userId` int(10) unsigned NOT NULL DEFAULT '0',
                  `ip` varchar(64) NOT NULL DEFAULT '',
                  `createdTime` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `keywordId` (`keywordId`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
            ");
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
