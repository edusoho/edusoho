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
       
         if(!$this->isTableExist('ip_blacklist')) {
            $connection->exec("CREATE TABLE `ip_blacklist` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `ip` varchar(32) NOT NULL,
                `type` enum('failed','banned') NOT NULL,
                `counter` int(10) unsigned NOT NULL DEFAULT '0',
                `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ");
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
