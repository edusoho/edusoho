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