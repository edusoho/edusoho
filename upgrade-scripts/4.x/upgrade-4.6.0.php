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

        if(!$this->isFieldExist('groups_thread', 'rewardCoin')){
            $connection->exec("ALTER TABLE `groups_thread` ADD `rewardCoin` INT(10) UNSIGNED NOT NULL DEFAULT '0' ;");
        }

        if(!$this->isFieldExist('groups_thread', 'type')){
            $connection->exec("ALTER TABLE `groups_thread` ADD `type` VARCHAR(255) NOT NULL DEFAULT 'default' AFTER `rewardCoin`;");
        }

        if(!$this->isFieldExist('groups_thread_post', 'adopt')){
            $connection->exec("ALTER TABLE `groups_thread_post` ADD `adopt` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `createdTime`;");
        }

        if(!$this->isFieldExist('user', 'lastPasswordFailTime')){
            $connection->exec("ALTER table `user` 
            Add column `lastPasswordFailTime` int(10) not null default 0 AFTER `locked`;");
        }

        $connection->exec("CREATE TABLE IF NOT EXISTS `groups_thread_trade` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `threadId` int(10) unsigned DEFAULT '0',
          `goodsId` int(10) DEFAULT '0',
          `userId` int(10) unsigned NOT NULL,
          `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $connection->exec("CREATE TABLE IF NOT EXISTS `groups_thread_goods` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `title` text NOT NULL,
            `description` text,
            `userId` int(10) unsigned NOT NULL DEFAULT '0',
            `type` enum('content','attachment','postAttachment') NOT NULL,
            `threadId` int(10) unsigned NOT NULL,
            `postId` int(10) unsigned NOT NULL DEFAULT '0',
            `coin` int(10) unsigned NOT NULL,
            `fileId` int(10) unsigned NOT NULL DEFAULT '0',
            `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
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