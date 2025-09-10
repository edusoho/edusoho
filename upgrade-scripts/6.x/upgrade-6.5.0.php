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
        
        if (!$this->isFieldExist('course', 'locked')) {
            $connection->exec("ALTER TABLE `course` ADD `locked` INT(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁'");
        }

        if (!$this->isFieldExist('course_lesson', 'parentId')) {
            $connection->exec("ALTER TABLE `course_lesson` ADD `parentId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id'");
        }

        if (!$this->isFieldExist('question', 'pId')) {
            $connection->exec("ALTER TABLE `question` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制问题对应Id'");
        }

        if (!$this->isFieldExist('testpaper', 'pId')) {
            $connection->exec("ALTER TABLE `testpaper` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id'");
        }

        if (!$this->isFieldExist('testpaper_item', 'pId')) {
            $connection->exec("ALTER TABLE `testpaper_item` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id'");
        }

        if (!$this->isFieldExist('course_material', 'pId')) {
            $connection->exec("ALTER TABLE `course_material` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id'");
        }

        if (!$this->isFieldExist('course_chapter', 'pId')) {
            $connection->exec("ALTER TABLE `course_chapter` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制章节的id'");  
        }

        if($this->isTableExist('homework')) {
            if(!$this->isFieldExist('homework', 'pId')){
              $connection->exec("ALTER TABLE `homework` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的作业Id'"); 
            }

            if(!$this->isFieldExist('homework_item', 'pId')){
              $connection->exec("ALTER TABLE `homework_item` ADD `pId`INT(10) NOT NULL DEFAULT '0' COMMENT '复制练习问题ID'"); 
            }

            if(!$this->isFieldExist('exercise', 'pId')){
              $connection->exec("ALTER TABLE `exercise` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制练习的ID'"); 
            }
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
