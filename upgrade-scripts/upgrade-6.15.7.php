<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Common\BlockToolkit;

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

        if ($this->isTableExist('friend')) {
            $connection->exec("UPDATE friend SET pair=1 WHERE id IN ( SELECT a.id id FROM (SELECT id,fromId,toId FROM friend) AS a, (SELECT id,fromId,toId FROM friend) AS b WHERE a.fromId=b.toId AND a.toId=b.fromId)");
        }

        if (!$this->isTableExist('discovery_column'))
            $connection->exec("
                CREATE TABLE `discovery_column` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `title` varchar(255) NOT NULL,
                  `type` varchar(32) NOT NULL COMMENT='栏目类型',
                  `categoryId` int(10) NOT NULL DEFAULT '0' COMMENT='分类',
                  `orderType` varchar(32) NOT NULL COMMENT='排序字段',
                  `showCount` int(10) NOT NULL COMMENT='展示数量',
                  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT='排序',
                  `createdTime` int(10) unsigned NOT NULL,
                  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发现页栏目';
            ");
        }

        global $kernel;

        //初始化系统编辑区
        BlockToolkit::init(realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/block.json"), $kernel->getContainer());
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
