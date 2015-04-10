<?php

use Symfony\Component\Filesystem\Filesystem;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
        $this->getConnection()->beginTransaction();
        try{
            $this->updateScheme();

            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }
     }

    private function updateScheme()
     {
        $connection = $this->getConnection();

        if(!$this->isFieldExist('course', 'originPrice')) {
            $connection->exec("ALTER TABLE `course` ADD COLUMN `originPrice` FLOAT(10,2) NOT NULL DEFAULT  0  AFTER `price`;");
        }
        if(!$this->isFieldExist('course', 'originCoinPrice')) {
            $connection->exec("ALTER TABLE `course` ADD COLUMN `originCoinPrice` FLOAT(10,2) NOT NULL DEFAULT  0  AFTER `coinPrice`;");
        }

        if(!$this->isFieldExist('course', 'discountId')) {
            $connection->exec("ALTER TABLE `course` ADD COLUMN `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '折扣活动ID' AFTER `userId`;");
        }

        if(!$this->isFieldExist('course', 'discount')) {
            $connection->exec("ALTER TABLE `course` ADD `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣' AFTER `discountId`;");
        }

        if(!$this->isFieldExist('orders', 'discountId')) {
            $connection->exec("ALTER TABLE  `orders` ADD  `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '折扣活动ID' AFTER `giftTo`;");
        }

        if(!$this->isFieldExist('orders', 'discount')) {
            $connection->exec("ALTER TABLE  `orders` ADD  `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣' AFTER  `discountId`;");
        }

        if (!$this->isTableExist('crontab_job')) {
            $connection->exec("
                CREATE TABLE `crontab_job` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
                  `name` varchar(1024) NOT NULL COMMENT '任务名称',
                  `cycle` enum('once') NOT NULL DEFAULT 'once' COMMENT '任务执行周期',
                  `jobClass` varchar(1024) NOT NULL COMMENT '任务的Class名称',
                  `jobParams` text NOT NULL COMMENT '任务参数',
                  `executing` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行状态',
                  `nextExcutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务下次执行的时间',
                  `latestExecutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务最后执行的时间',
                  `creatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '任务创建时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        $connection->exec("UPDATE `course` SET `originPrice` = `price`;");
        $connection->exec("UPDATE `course` SET `originCoinPrice` = `coinPrice`;");

    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
 }


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