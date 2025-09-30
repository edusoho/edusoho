<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
         $this->getConnection()->beginTransaction();
         try {
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
     }

     private function updateScheme()
     {
        $connection = $this->getConnection();

        $connection->exec("UPDATE `course` c set income=(select sum(amount) from orders where targetId=c.id and targetType='course' and status in ('paid','refunding','refunded')) where id in (select distinct targetId from orders where targetType='course' and status in ('paid','refunding','refunded'));");

        if($this->isTableExist('classroom')) {
            $connection->exec("UPDATE `classroom` c set income=(select sum(amount) from orders where targetId=c.id and targetType='classroom' and status in ('paid','refunding','refunded')) where id in (select distinct targetId from orders where targetType='classroom' and status in ('paid','refunding','refunded'));");
        }

        if(!$this->isFileGroupExist('block')){
            $connection->exec("INSERT INTO `file_group` (`name`, `code`, `public`) VALUES ('编辑区', 'block', '1');");
        }

        $connection->exec("ALTER TABLE `crontab_job` CHANGE `cycle` `cycle` ENUM('once','everyhour','everyday','everymonth') NOT NULL DEFAULT 'once' COMMENT '任务执行周期';");

        if(!$this->isFieldExist('crontab_job', 'cycleTime')){
            $connection->exec("ALTER TABLE `crontab_job` ADD `cycleTime` VARCHAR(255) NOT NULL DEFAULT '0' COMMENT '任务执行时间' AFTER `cycle`;");
        }

        if(!$this->isCrontabJobExist('CancelOrderJob')) {
            $connection->exec("INSERT INTO `crontab_job` (`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('CancelOrderJob', 'everyhour', '0', 'Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob', '', '0', '".time()."', '0', '0', '0');");
        }

        $this->getSettingService()->set("crontab_next_executed_time", time());
         
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

    protected function isFileGroupExist($code)
    {
        $sql = "select * from file_group where code='{$code}'";
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
