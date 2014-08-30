<?php

use Symfony\Component\Filesystem\Filesystem;

 class EduSohoUpgrade extends AbstractUpdater
 {
    public function update()
    {
        $this->updateScheme();
    }

     private function updateScheme()
     {
        $connection = $this->getConnection();

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `groups` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '小组id',
              `title` varchar(100) NOT NULL COMMENT '小组名称',
              `about` text COMMENT '小组介绍',
              `logo` varchar(100) NOT NULL DEFAULT '' COMMENT 'logo',
              `backgroundLogo` varchar(100) NOT NULL DEFAULT '',
              `status` enum('open','close') NOT NULL DEFAULT 'open',
              `memberNum` int(10) unsigned NOT NULL DEFAULT '0',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `ownerId` int(10) unsigned NOT NULL COMMENT '小组组长id',
              `createdTime` int(11) unsigned NOT NULL COMMENT '创建小组时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `groups_member` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '成员id主键',
              `groupId` int(10) unsigned NOT NULL COMMENT '小组id',
              `userId` int(10) unsigned NOT NULL COMMENT '用户id',
              `role` varchar(100) NOT NULL DEFAULT 'member',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(11) unsigned NOT NULL COMMENT '加入时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `groups_thread` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '话题id',
              `title` varchar(1024) NOT NULL COMMENT '话题标题',
              `content` text COMMENT '话题内容',
              `isElite` int(11) unsigned NOT NULL DEFAULT '0',
              `isStick` int(11) unsigned NOT NULL DEFAULT '0',
              `lastPostMemberId` int(10) unsigned NOT NULL,
              `lastPostTime` int(10) unsigned NOT NULL,
              `groupId` int(10) unsigned NOT NULL,
              `userId` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL COMMENT '添加时间',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `status` enum('open','close') NOT NULL DEFAULT 'open',
              `hitNum` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `groups_thread_post` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
              `threadId` int(11) unsigned NOT NULL COMMENT '话题id',
              `content` text NOT NULL COMMENT '回复内容',
              `userId` int(10) unsigned NOT NULL COMMENT '回复人id',
              `postId` int(10) unsigned DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL COMMENT '回复时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");

        if (!$this->isFieldExist('course', 'freeStartTime')) {
            $connection->exec("ALTER TABLE `course` ADD `freeStartTime` INT(10) NOT NULL DEFAULT 0 , ADD `freeEndTime` INT(10) NOT NULL DEFAULT 0");
        }

        if (!$this->isFieldExist('session', 'user_id')) {
            $connection->exec( "ALTER TABLE `session` ADD `user_id` INT(10) NOT NULL DEFAULT 0 AFTER `session_time`;");
        }

        if ($this->isFieldExist('user', 'title')) {
            $connection->exec( "ALTER TABLE  `user` CHANGE  `title`  `title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '' COMMENT  '头像'");
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
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