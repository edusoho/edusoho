<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

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

        //Version20150630200940
        if($this->isFieldExist('orders','payment')){
            $connection->exec("ALTER TABLE  `orders` CHANGE  `payment`  `payment` ENUM(  'none', 'alipay', 'tenpay', 'coin', 'wxpay' )  NOT NULL;");
        }

        if($this->isFieldExist('cash_orders','payment')){
            $connection->exec("ALTER TABLE  `cash_orders` CHANGE  `payment`  `payment` ENUM(  'none', 'alipay', 'wxpay' ) NOT NULL;");
        }

        if(!$this->isFieldExist('cash_flow','payment')){
            $connection->exec("ALTER TABLE  `cash_flow` ADD `payment` ENUM( 'alipay', 'wxpay' ) AFTER `category`;");
        }

        if($this->isFieldExist('cash_flow','payment')){
            $connection->exec("UPDATE `cash_flow` set `payment` = 'alipay' where `type` = 'inflow';");
        }
        //Version20150716101002
        if($this->isTableExist('homework')) {
            $connection->exec("delete from homework where lessonId not in (select id from course_lesson);");
        }
        if($this->isTableExist('exercise')){
            $connection->exec("delete from exercise where lessonId not in (select id from course_lesson);");
        }

        //Version20150716103422
        if(!$this->isCrontabJobExist("DeleteExpiredTokenJob")){
            $connection->exec("INSERT INTO `crontab_job`(`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('DeleteExpiredTokenJob','everyhour',0,'Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob','',0,".time().",0,0,0) ;");
        }
        
        if(!$this->isCrontabJobExist("DeleteSessionJob")){
            $connection->exec("INSERT INTO `crontab_job`(`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('DeleteSessionJob','everyhour',0,'Topxia\\\\Service\\\\User\\\\Job\\\\DeleteSessionJob','',0,".time().",0,0,0) ;");
        } else {
            $connection->exec("UPDATE `crontab_job` SET `jobClass`='Topxia\\\\Service\\\\User\\\\Job\\\\DeleteSessionJob' WHERE `name`='DeleteSessionJob';");
        }
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
