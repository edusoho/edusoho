<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;
use Symfony\Component\Yaml\Yaml;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
         $this->getConnection()->beginTransaction();
         try {
             $this->updateConfig();
             $this->updateScheme();
             $this->getConnection()->commit();
         } catch (\Exception $e) {
             $this->getConnection()->rollback();
             throw $e;
         }

         try {
             $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
             $filesystem = new Filesystem();

             if (!empty($dir)) {
                 $filesystem->remove($dir);
             }
         } catch (\Exception $e) {
         }

         $developerSetting = $this->getSettingService()->get('developer', array());
         $developerSetting['debug'] = 0;

         ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
         ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());

    }

   
    private function updateConfig()
    {
        $filePath = $this->kernel->getParameter('kernel.root_dir').'/config/parameters.yml';
        $fileContent = file_get_contents($filePath);
        $yaml = new Yaml();
        $config = $yaml->parse($fileContent);

        if(!isset($config['parameters']['database_port']) || !is_numeric($config['parameters']['database_port'])){
            $config['parameters']['database_port'] = 3306;
            $content = $yaml->dump($config);
            $fh = fopen($filePath,"w");
            fwrite($fh,$content);
            fclose($fh);
        }
            
    }
    private function updateScheme()
    {
        $connection = $this->getConnection();
        
        if (!$this->isTableExist('blacklist')) {
            $connection->exec("
                CREATE TABLE `blacklist` (
                 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
                 `userId` int(10) unsigned NOT NULL COMMENT '名单拥有者id',
                 `blackId` int(10) unsigned NOT NULL COMMENT '黑名单用户id',
                 `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入黑名单时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';
            ");
        }

        if (!$this->isFieldExist('message', 'type')) {
            $connection->exec("ALTER TABLE `message` ADD `type` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '私信类型' AFTER `id`;");
        }

        if (!$this->isFieldExist('message_conversation', 'latestMessageType')) {
            $connection->exec("ALTER TABLE `message_conversation` ADD `latestMessageType` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '最后一条私信类型' AFTER `latestMessageContent`;");
        }
        
        if (!$this->isFieldExist('friend', 'pair')) {
            $connection->exec("ALTER TABLE `friend` ADD `pair` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否为互加好友' AFTER `toId`;");
        }

        $connection->exec("UPDATE friend SET pair=1 WHERE id IN ( SELECT a.id id FROM (SELECT id,fromId,toId FROM friend) AS a, (SELECT id,fromId,toId FROM friend) AS b WHERE a.fromId=b.toId AND a.toId=b.fromId)");
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

    
    
    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
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
