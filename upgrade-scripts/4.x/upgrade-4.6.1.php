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

        if(!$this->isFieldExist('user', 'consecutivePasswordErrorTimes')){
            $connection->exec("ALTER table `user` Add column `consecutivePasswordErrorTimes` int not null default 0 AFTER `locked`;");
        }

        if(!$this->isFieldExist('user', 'lockDeadline')){
            $connection->exec("ALTER table `user` Add column `lockDeadline` int(10) not null default 0 AFTER `locked`;");
        }

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `upload_files_share` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `sourceUserId` int(10) UNSIGNED NOT NULL COMMENT '上传文件的用户ID',
              `targetUserId` int(10) UNSIGNED NOT NULL COMMENT '文件分享目标用户ID',
              `isActive` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否有效',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");
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