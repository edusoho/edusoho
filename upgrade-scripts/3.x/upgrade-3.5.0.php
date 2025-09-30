<?php

use Symfony\Component\Filesystem\Filesystem;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
        $this->getConnection()->beginTransaction();
        try{
            $this->updateScheme();

            // $developerSetting = $this->getSettingService()->get('developer', array());
            // $developerSetting['hls_encrypted'] = 1;
            // $this->getSettingService()->set('developer', $developerSetting);

            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }
     }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

     private function updateScheme()
     {
        $connection = $this->getConnection();

        if (!$this->isFieldExist('user_token', 'times')) {
            $connection->exec("ALTER TABLE  `user_token` ADD  `times` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'TOKEN的校验次数限制(0表示不限制)' AFTER  `data`");
        }

        if (!$this->isFieldExist('user_token', 'remainedTimes')) {
            $connection->exec("ALTER TABLE  `user_token` ADD  `remainedTimes` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'TOKE剩余校验次数' AFTER  `times`");
        }

        if (!$this->isFieldExist('upload_files', 'length')) {
            $connection->exec("ALTER TABLE  `upload_files` ADD  `length` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '长度（音视频则为时长，PPT/文档为页数）' AFTER  `etag`");
        }

        if (!$this->isFieldExist('testpaper', 'passedScore')) {
            $connection->exec("ALTER TABLE  `testpaper` ADD  `passedScore` FLOAT( 10, 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '通过考试的分数线' AFTER  `score`");
        }

        if (!$this->isFieldExist('testpaper_result', 'passedStatus')) {
            $connection->exec("ALTER TABLE  `testpaper_result` ADD  `passedStatus` ENUM(  'none',  'passed',  'unpassed' ) NOT NULL DEFAULT  'none' COMMENT  '考试通过状态，none表示该考试没有' AFTER  `rightItemCount`");
        }

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `mobile_device` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '设备ID',
              `imei` varchar(255) NOT NULL COMMENT '串号',
              `platform` varchar(255) NOT NULL COMMENT '平台',
              `version` varchar(255) NOT NULL COMMENT '版本',
              `screenresolution` varchar(100) NOT NULL COMMENT '分辨率',
              `kernel` varchar(255) NOT NULL COMMENT '内核',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
     }

     private function getSettingService() 
     {
        return $this->createService('System.SettingService');
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