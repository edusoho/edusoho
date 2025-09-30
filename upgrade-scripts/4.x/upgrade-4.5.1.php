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

        $connection->exec("
          CREATE TABLE IF NOT EXISTS `groups_thread_collect` (
          `id` int(10) unsigned NOT NULL auto_increment COMMENT 'id主键',
          `threadId` int(11) unsigned NOT NULL COMMENT '收藏的话题id',
          `userId` int(10) unsigned NOT NULL COMMENT '收藏人id',
          `createdTime` int(10) unsigned NOT NULL COMMENT '收藏时间',
          PRIMARY KEY  (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
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