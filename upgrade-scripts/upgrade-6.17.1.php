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
        if(!$this->isTableExist('dictionary_item')){
            $this->getConnection()->exec("CREATE TABLE `dictionary_item` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
             `type` varchar(255) NOT NULL COMMENT '字典类型',
             `code` varchar(64) DEFAULT NULL COMMENT '编码',
             `name` varchar(255) NOT NULL COMMENT '字典内容名称',
             `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
             `createdTime` int(10) unsigned NOT NULL,
             `updateTime` int(10) unsigned DEFAULT '0',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
            ");

            $this->getConnection()->exec("INSERT INTO `dictionary_item` (`type`, `code`, `name`, `weight`, `createdTime`, `updateTime`) VALUES ('refund_reason', NULL, '课程内容质量差', '0', '0', '0');");

            $this->getConnection()->exec("INSERT INTO `dictionary_item` (`type`, `code`, `name`, `weight`, `createdTime`, `updateTime`) VALUES ('refund_reason', NULL, '老师服务态度不好', '0', '0', '0');");
        }


        if(!$this->isTableExist('dictionary')){
            $this->getConnection()->exec("CREATE TABLE `dictionary` (
                         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                         `name` varchar(255) NOT NULL COMMENT '字典名称',
                         `type` varchar(255) NOT NULL COMMENT '字典类型',
                         PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");

            $this->getConnection()->exec("INSERT INTO `dictionary` (`name`, `type`) VALUES ('退学原因', 'refund_reason');");
        }


        global $kernel;

        BlockToolkit::init(realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/jianmo/block.json"), $kernel->getContainer());
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
