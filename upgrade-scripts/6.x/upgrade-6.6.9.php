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
             $this->updateScheme();
             $this->getConnection()->commit();

             $this->updateCrontabSetting();
         } catch (\Exception $e) {
             $this->getConnection()->rollback();
             throw $e;
         }

        try {
             $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
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

    private function updateScheme()
    {
        $connection = $this->getConnection();
        
        $connection->exec("update upload_files set usedCount=0");
        $connection->exec("update upload_files as file, 
                        (SELECT mediaId,COUNT(id) coun from course_lesson where type<>'live' and mediaSource='self' and mediaId is not null group by mediaId) as t1
                        set file.usedCount = t1.coun+file.usedCount where t1.mediaId = file.id");
        $connection->exec("update upload_files as file, 
                        (SELECT fileId,COUNT(id) coun from course_material group by fileId) as t2 
                        set file.usedCount = t2.coun+file.usedCount where t2.fileId = file.id");

        if (!$this->isTableExist('sessions')){
            $connection->exec("CREATE TABLE `sessions` (
                    `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
                    `sess_user_id` INT UNSIGNED NOT NULL DEFAULT  '0',
                    `sess_data` BLOB NOT NULL,
                    `sess_time` INTEGER UNSIGNED NOT NULL,
                    `sess_lifetime` MEDIUMINT NOT NULL
                ) COLLATE utf8_bin, ENGINE = InnoDB;");
        }
        

    }

    private function updateCrontabSetting()
    {
        $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/data/crontab_config.yml");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
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
