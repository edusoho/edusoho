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

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `cash_orders_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `orderId` int(10) unsigned NOT NULL,
            `message` text,
            `data` text,
            `userId` int(10) unsigned NOT NULL DEFAULT '0',
            `ip` varchar(255) NOT NULL,
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
            `type` varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `cash_orders` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `sn` varchar(32) NOT NULL COMMENT '订单号',
            `status` enum('created','paid','cancelled') NOT NULL,
            `title` varchar(255) NOT NULL,
            `amount` float(10,2) unsigned NOT NULL DEFAULT '0.00',
            `payment` enum('none','alipay') NOT NULL DEFAULT 'none',
            `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
            `note` varchar(255) NOT NULL DEFAULT '',
            `userId` int(10) unsigned NOT NULL DEFAULT '0',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `cash_account` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(10) unsigned NOT NULL,
                `cash` float(10,2) NOT NULL DEFAULT '0.00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `userId` (`userId`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `cash_flow` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '账号ID，即用户ID',
              `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
              `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
              `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
              `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
              `orderSn` varchar(40) NOT NULL COMMENT '订单号',
              `category` varchar(128) NOT NULL DEFAULT '' COMMENT '帐目类目',
              `note` text COMMENT '备注',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `tradeNo` (`sn`),
              UNIQUE KEY `orderSn` (`orderSn`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='帐目流水' AUTO_INCREMENT=1 ;
        ");

        if(!$this->isFieldExist("course","coinPrice")){
          $connection->exec("
              ALTER TABLE `course` ADD `coinPrice` FLOAT(10,2) NOT NULL DEFAULT 0.00 AFTER `price`;
          ");
        }

        $connection->exec("
            ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'none';
        ");

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