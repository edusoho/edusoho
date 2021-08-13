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

        $connection->exec("ALTER TABLE upload_files MODIFY targetId INT(11) ;");
        $connection->exec("ALTER TABLE upload_files CHANGE targetType targetType VARCHAR(64) NULL");

        if(!$this->isFieldExist('upload_files', 'usedCount')){
            $connection->exec("ALTER TABLE upload_files ADD usedCount int(10) unsigned NOT NULL DEFAULT 0 AFTER `canDownload`;");
        }
        
        $connection->exec("UPDATE upload_files files, (SELECT count(*) AS co,mediaId FROM course_lesson WHERE type IN ('video','audio','ppt') GROUP BY mediaId) filesUsedCount SET files.usedCount = files.usedCount+filesUsedCount.co WHERE files.id=filesUsedCount.mediaId;");
        $connection->exec("UPDATE upload_files files,(SELECT count(*) AS co,fileId FROM course_material GROUP BY fileId) filesUsedCount SET files.usedCount = files.usedCount+filesUsedCount.co WHERE files.id=filesUsedCount.fileId;");

        $connection->exec("CREATE TABLE IF NOT EXISTS `upload_files_share` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `sourceUserId` int(10) unsigned NOT NULL COMMENT '上传文件的用户ID',
            `targetUserId` int(10) unsigned NOT NULL COMMENT '文件分享目标用户ID',
            `isActive` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否有效',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            PRIMARY KEY (`id`),
            KEY `sourceUserId` (`sourceUserId`),
            KEY `targetUserId` (`targetUserId`),
            KEY `createdTime` (`createdTime`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"
        );

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