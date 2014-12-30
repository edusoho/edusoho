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


      if($this->isTableExist('vip_history') && !$this->isFieldExist('vip_history', 'priceType')){
        $connection->exec(" 
        ALTER TABLE `vip_history` ADD `priceType` ENUM('RMB','Coin') NOT NULL DEFAULT 'RMB' ;
        ");
        //UPDATE
        $connection->exec("UPDATE vip_history SET `priceType`='RMB';");
      }

      if($this->isTableExist('vip_level') && !$this->isFieldExist('vip_level', 'monthCoinPrice')){
        $connection->exec("
          ALTER TABLE `vip_level` ADD `monthCoinPrice` FLOAT(10,2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `yearPrice`;  
        ");
      }

      if($this->isTableExist('vip_level') && !$this->isFieldExist('vip_level', 'yearCoinPrice')){
        $connection->exec(" 
        ALTER TABLE `vip_level` ADD `yearCoinPrice` FLOAT(10,2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `monthCoinPrice`;
        ");
      }

      if($this->isIndexExist('cash_flow', 'orderSn')){
        $connection->exec("DROP INDEX orderSn ON cash_flow;");
      }

      $connection->exec("CREATE TABLE IF NOT EXISTS `money_card` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `cardId` varchar(32) NOT NULL,
          `password` varchar(32) NOT NULL,
          `deadline` varchar(19) NOT NULL COMMENT '有效时间',
          `rechargeTime` int(10) NOT NULL COMMENT '充值时间，0为未充值',
          `cardStatus` enum('normal','invalid','recharged') NOT NULL,
          `rechargeUserId` int(11) NOT NULL,
          `batchId` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


      $connection->exec("CREATE TABLE IF NOT EXISTS `money_card_batch` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `cardPrefix` varchar(32) NOT NULL,
          `cardLength` int(8) NOT NULL,
          `number` int(11) NOT NULL,
          `rechargedNumber` int(11) NOT NULL,
          `deadline` varchar(19) CHARACTER SET latin1 NOT NULL,
          `money` int(8) NOT NULL,
          `userId` int(11) NOT NULL,
          `createdTime` int(11) NOT NULL,
          `note` varchar(128) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      if(!$this->isFieldExist('money_card_batch', 'coin')){
        $connection->exec("
            ALTER TABLE `money_card_batch` 
            ADD COLUMN `coin` int NOT NULL DEFAULT 0 AFTER `money`;
        ");
      }

      if(!$this->isFieldExist('money_card_batch', 'batchName')){
        $connection->exec("
            ALTER TABLE `money_card_batch` 
            ADD COLUMN `batchName` VARCHAR(15) NOT NULL DEFAULT '' AFTER `note`;
        "); 
      }

      $connection->exec("
        ALTER TABLE `money_card_batch` 
          CHANGE COLUMN `cardLength` `cardLength` INT(8) NOT NULL DEFAULT 0 ,
          CHANGE COLUMN `number` `number` INT(11) NOT NULL DEFAULT 0 ,
          CHANGE COLUMN `rechargedNumber` `rechargedNumber` INT(11) NOT NULL DEFAULT 0 ,
          CHANGE COLUMN `money` `money` INT(8) NOT NULL DEFAULT 0 ,
          CHANGE COLUMN `userId` `userId` INT(11) NOT NULL DEFAULT 0 ,
          CHANGE COLUMN `createdTime` `createdTime` INT(11) NOT NULL DEFAULT 0 ;
      ");

      $connection->exec("
        ALTER TABLE `money_card` 
          CHANGE COLUMN `rechargeTime` `rechargeTime` INT(10) NOT NULL DEFAULT 0 COMMENT '充值时间，0为未充值' ,
          CHANGE COLUMN `cardStatus` `cardStatus` ENUM('normal','invalid','recharged') NOT NULL DEFAULT 'invalid' ,
          CHANGE COLUMN `rechargeUserId` `rechargeUserId` INT(11) NOT NULL DEFAULT 0 ,
          CHANGE COLUMN `batchId` `batchId` INT(11) NOT NULL DEFAULT 0 ;
      ");

      if(!$this->isFieldExist('money_card_batch', 'batchStatus')){
        $connection->exec("
            ALTER TABLE `money_card_batch` 
            ADD COLUMN `batchStatus` ENUM('invalid','normal') NOT NULL DEFAULT 'normal' AFTER `batchName`;
        "); 
      }


      $this->getConnection()->beginTransaction();
      try{
        $this->process();
        $this->getConnection()->commit();
      } catch(\Exception $e) {
        $this->getConnection()->rollback();
      }
    }

    protected function process(){
      $setting = $this->initSetting();
      $processInfo = $setting["value"];
      if(empty($setting) || $processInfo["maxCreatedTime"] == $processInfo["processedCreatedTime"]) {
        return ;
      }

      $order = $this->proccessOrderCashFlow($processInfo["maxCreatedTime"], $processInfo["processedCreatedTime"], 0,  20000);

      $this->updateSetting($order, "orders");

    }

    protected function initSetting($processedCreatedTime = 0)
    {
      $result = $this->getSettingByName("orders");

      if(empty($result)){
    $sql = "select * from orders where createdTime = (SELECT max(createdTime) FROM `orders` where status='paid' and amount>0) LIMIT 1;";
    $result = $this->getConnection()->fetchAssoc($sql);
    if(!empty($result)) {
      return $this->addSetting($result["id"], $result["createdTime"]);
    } 
        return null;
      } else {
        $result["value"] = unserialize($result["value"]);
        return $result;
      }

    }

    protected function addSetting($id, $createdTime, $processedCreatedTime = 0)
    {
      $value = array(
        "id"=>$id,
        "maxCreatedTime" => $createdTime,
        "processedCreatedTime" => $processedCreatedTime
      );

      $setting = array(
        'name'  => 'orders',
        'value' => serialize($value)
      );

      $this->getConnection()->insert('setting', $setting);

      return $this->getSetting($this->getConnection()->lastInsertId());
    }

    protected function getSetting($id)
    {
        $sql = "SELECT * FROM setting WHERE id = ? LIMIT 1";
        $setting = $this->getConnection()->fetchAssoc($sql, array($id));
        $setting["value"] = unserialize($setting["value"]);
        return $setting;
    }

    protected function proccessOrderCashFlow($createdTime, $processedCreatedTime, $start,  $count)
    {
      $sql = "select * from (select * from orders where createdTime <= {$createdTime} and createdTime > {$processedCreatedTime} and status='paid' and amount>0 order by id) orders LIMIT {$start}, {$count};";
      $orders = $this->getConnection()->fetchAll($sql, array());

      if(empty($orders) || count($orders)==0){
        return ;
      }

      foreach ($orders as $key => $order) {

        $currentTime = date('YmdHis');
        if($this->time != $currentTime){
          $this->time = $currentTime;
          $this->num = 0;
        } else {
          $this->num++;
        }

        $number = sprintf("%05d", $this->num);

        $cashFlow = array(
          "userId"=>$order["userId"],
          "sn"=> ($this->time . $number),
          "type"=>"inflow",
          "amount"=>$order["amount"],
          "cashType"=>"RMB",
          "orderSn"=>$order["sn"],
          "category"=>"inflow",
          'name' => '入账',
          "createdTime"=>$order["createdTime"],
        );
        $inflow = $this->getConnection()->insert('cash_flow', $cashFlow);

        $this->num++;
        $number = sprintf("%05d", $this->num);

        $cashFlow = array(
          "userId"=>$order["userId"],
          "sn"=> ($this->time . $number),
          "type"=>"outflow",
          "amount"=>$order["amount"],
          "cashType"=>"RMB",
          'name' => '出账',
          "orderSn"=>$order["sn"],
          "category"=>"outflow",
          "createdTime"=>$order["createdTime"],
          "parentSn" => $inflow["sn"]
        );
        $cashFlow = $this->getConnection()->insert('cash_flow', $cashFlow);

        $fields = array("cashSn" => $cashFlow["sn"]);
        $this->getConnection()->update("orders", $fields, array('id' => $order["id"]));
      }

      return $orders[count($orders)-1];
    }

    protected function updateSetting($order, $name)
    {
        $setting = $this->getSettingByName($name);
        $processInfo = unserialize($setting["value"]);
        $this->getConnection()->delete("setting", array('name' => "orders"));
        $this->addSetting($processInfo["id"], $processInfo["maxCreatedTime"], $order["createdTime"]);
    }

    protected function getSettingByName($name)
    {
        $sql = "SELECT * FROM setting WHERE name = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($name));
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