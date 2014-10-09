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
            CREATE TABLE `status` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '动态发布的人',
              `type` varchar(64) NOT NULL COMMENT '动态类型',
              `objectType` varchar(64) NOT NULL DEFAULT '' COMMENT '动态对象的类型',
              `objectId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态对象ID',
              `message` text NOT NULL COMMENT '动态的消息体',
              `properties` text NOT NULL COMMENT '动态的属性',
              `commentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
              `likeNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被赞的数量',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态发布时间',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`),
              KEY `createdTime` (`createdTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
        
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