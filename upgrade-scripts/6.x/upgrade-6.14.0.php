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

        if (!$this->isTableExist('marker')) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS `marker` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `second` int(10) unsigned NOT NULL COMMENT '驻点时间',
                  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='驻点';
            ");
        }

        if (!$this->isTableExist('question_marker')) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS `question_marker` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
                    `questionId` int(10) unsigned NOT NULL COMMENT '问题Id',
                    `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
                    `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
                    `stem` text COMMENT '题干',
                    `answer` text COMMENT '参考答案',
                    `analysis` text COMMENT '解析',
                    `metas` text COMMENT '题目元信息',
                    `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
                    `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                    `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='弹题';
            ");
        }

        if (!$this->isTableExist('question_marker_result')) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS `question_marker_result` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
                    `questionMarkerId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '弹题ID',
                    `lessonId` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                    `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做题人ID',
                    `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none' COMMENT '结果状态',
                    `answer` text  DEFAULT NULL ,
                    `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                    `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('task', 'intervalDate')) {
            $connection->exec("
                ALTER TABLE `task` ADD `intervalDate` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '历时天数' AFTER `taskEndTime`;
            ");
        }

        if ($this->isFieldExist('orders', 'payment')) {
            $connection->exec(" ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay','quickpay','iosiap') CHARACTER SET utf8  NOT NULL");
        }

        if ($this->isFieldExist('cash_orders', 'payment')) {
            $connection->exec(" ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay','quickpay','iosiap') CHARACTER SET utf8 NOT NULL");
        }

        if ($this->isFieldExist('cash_flow', 'payment')) {
            $connection->exec(" ALTER TABLE `cash_flow` CHANGE `payment` `payment` ENUM('alipay','wxpay','heepay','quickpay','iosiap') CHARACTER SET utf8  NULL DEFAULT NULL");
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
