<?php

use Symfony\Component\Filesystem\Filesystem;

 class EduSohoUpgrade extends AbstractUpdater
 {
    protected $time ;

    protected $num = 0;

    public function update()
    {
      $this->time = date('YmdHis');
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

      if(!$this->isFieldExist('user', 'payPasswordSalt')){
        $connection->exec("ALTER table `user` 
        Add column `payPasswordSalt` varchar(64) NOT NULL DEFAULT '' AFTER `salt`;");
      }

      if(!$this->isFieldExist('user', 'payPassword')){
        $connection->exec("ALTER table `user` 
        Add column `payPassword` varchar(64) NOT NULL DEFAULT '' AFTER `salt`;");
      }

      $connection->exec("CREATE TABLE IF NOT EXISTS `user_secure_question` (
        `id` int(10) unsigned NOT NULL auto_increment ,
        `userId` int(10) unsigned NOT NULL DEFAULT 0,
        `securityQuestionCode` varchar(64) NOT NULL DEFAULT '',
        `securityAnswer` varchar(64) NOT NULL DEFAULT '',
        `securityAnswerSalt` varchar(64) NOT NULL DEFAULT '',
        `createdTime` int(10) unsigned NOT NULL DEFAULT '0',       
        PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

      if(!$this->isFieldExist('orders', 'coinAmount')){
        $connection->exec("ALTER TABLE `orders` ADD `coinAmount` FLOAT(10,2) NOT NULL DEFAULT '0' AFTER `payment`;");
      }

      if(!$this->isFieldExist('orders', 'totalPrice')){
        $connection->exec("ALTER TABLE `orders` ADD `totalPrice` FLOAT(10,2) NOT NULL DEFAULT '0' AFTER `amount`;");
        //UPDATE
        $connection->exec("UPDATE orders SET `totalPrice`=`amount`;");
      }
      if(!$this->isFieldExist('orders', 'coinRate')){
        $connection->exec("ALTER TABLE `orders` ADD `coinRate` FLOAT(10,2) NOT NULL DEFAULT '1'  AFTER `coinAmount`;");
      }
      if(!$this->isFieldExist('orders', 'priceType')){
        $connection->exec("ALTER TABLE `orders` ADD `priceType` enum('RMB','Coin') NOT NULL DEFAULT 'RMB' AFTER `coinRate`;");
        //UPDATE
        $connection->exec("UPDATE orders SET `priceType`='RMB';");
      }

      if(!$this->isFieldExist('orders', 'cashSn')){
        $connection->exec("ALTER TABLE `orders` ADD `cashSn` BIGINT(20) NULL AFTER `paidTime`;");
      }

      if(!$this->isFieldExist('cash_flow', 'cashType')){
        $connection->exec("ALTER TABLE `cash_flow` ADD `cashType` ENUM('RMB','Coin') NOT NULL DEFAULT 'Coin' AFTER `amount`;");
        //UPDATE
        $connection->exec("UPDATE cash_flow SET `cashType`='Coin';");
      }
      if(!$this->isFieldExist('cash_flow', 'cash')){
        $connection->exec("ALTER TABLE `cash_flow` ADD `cash` FLOAT(10,2) NOT NULL DEFAULT '0' AFTER `cashType`;");
        //TODO
      }
      if(!$this->isFieldExist('cash_flow', 'parentSn')){
        $connection->exec("ALTER TABLE `cash_flow` ADD `parentSn` bigint(20) NULL AFTER `cashType`;");
        //TODO
      }

      $time = time();

      if(!$this->isBlockDataExist()){
        $connection->exec("
        INSERT INTO `block` (`userId`, `title`, `mode`, `content`, `code`, `createdTime`, `updateTime`) 
        VALUES ('1', '我的账户Banner', 'html', 
        '<br>\n<div class=\"col-md-12\">\n  
        <a href=\"#\"><img src=\"/assets/img/placeholder/banner-wallet.png\" /></a>
        <br>\n<br>\n</div>', 'bill_banner','{$time}','{$time}');
        "); 
      }


      if($this->isIndexExist('cash_flow', 'orderSn')){
        $connection->exec("DROP INDEX orderSn ON cash_flow;");
      }

    }

    protected function isBlockDataExist()
    {
        $sql = "select * from block where code='bill_banner' LIMIT 1;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
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