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
        $connection->exec("CREATE TABLE IF NOT EXISTS `thread` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `targetType` varchar(255) NOT NULL DEFAULT 'classroom_thread' COMMENT '所属 类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属类型 ID',
              `title` varchar(255) NOT NULL COMMENT '标题',
              `content` text COMMENT '内容',
              `nice` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加精',
              `sticky` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
              `lastPostUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人ID',
              `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `type` varchar(255) NOT NULL DEFAULT '' COMMENT '话题类型',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
              `status` enum('open','closed') NOT NULL DEFAULT 'open' COMMENT '状态',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题最后一次被编辑或回复时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `thread_post` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `threadId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题ID',
              `content` text NOT NULL COMMENT '内容',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"
                );

        $connection->exec("ALTER TABLE `thread` CHANGE `targetType` `targetType` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'classroom' COMMENT '所属 类型';");
        
        if (!$this->isFieldExist('thread_post', 'targetType' ))
        $connection->exec("
        ALTER TABLE `thread_post` ADD `targetType` VARCHAR(255) NOT NULL DEFAULT 'classroom' COMMENT '所属 类型', ADD `targetId` INT(10) UNSIGNED NOT NULL COMMENT '所属 类型ID';   ");
        
        if (!$this->isFieldExist('thread', 'relationId' ))
        $connection->exec("
        ALTER TABLE `thread` ADD `relationId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '从属ID' AFTER `targetId` , ADD `categoryId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID' AFTER `relationId` ; ");

        if (!$this->isFieldExist('course', 'useInClassroom' ))
        $connection->exec("ALTER TABLE `course` ADD `useInClassroom` ENUM('single','more') NOT NULL DEFAULT 'single' COMMENT '课程能否用于多个班级' AFTER `vipLevelId`, ADD `singleBuy` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '加入班级后课程能否单独购买' AFTER `useInClassroom`;");
        
        if (!$this->isFieldExist('course_member', 'classroomId' ))
        $connection->exec("ALTER TABLE `course_member` ADD `classroomId` INT(10) NOT NULL DEFAULT '0'  COMMENT '班级ID' AFTER `courseId`; ");

        if (!$this->isFieldExist('course_member', 'joinedType' ))
        $connection->exec("ALTER TABLE `course_member` ADD `joinedType` ENUM('course','classroom') NOT NULL DEFAULT 'course' COMMENT '购买班级或者课程加入学习' AFTER `classroomId`; ");
        $connection->exec("CREATE TABLE IF NOT EXISTS `sign_target_statistics` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
              `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
              `signedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到人数',
              `date` int(6) unsigned NOT NULL DEFAULT '0' COMMENT '统计日期',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
        $connection->exec("CREATE TABLE IF NOT EXISTS `sign_user_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
              `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
              `rank` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到排名',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
        $connection->exec("CREATE TABLE IF NOT EXISTS `sign_user_statistics` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
              `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
              `keepDays` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '连续签到天数',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
        $connection->exec( "CREATE TABLE IF NOT EXISTS `sign_card` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `cardNum` int(10) unsigned NOT NULL DEFAULT '0',
              `useTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
        
        if (!$this->isFieldExist('thread_post', 'subposts' ))
        $connection->exec("ALTER TABLE  `thread_post` ADD  `subposts` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '子话题数量' AFTER  `parentId`;");
    
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `thread_vote` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `threadId` int(10) unsigned NOT NULL COMMENT '话题ID',
              `postId` int(10) unsigned NOT NULL COMMENT '回帖ID',
              `action` enum('up','down') NOT NULL COMMENT '投票类型',
              `userId` int(10) unsigned NOT NULL COMMENT '投票人ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '投票时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `postId` (`threadId`,`postId`,`userId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='话题投票表';
        ");
        if (!$this->isFieldExist('thread_post', 'ups' ))
        $connection->exec("ALTER TABLE  `thread_post` ADD  `ups` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '投票数' AFTER  `subposts`");
        
        if (!$this->isFieldExist('thread', 'ats' ))
        $connection->exec("ALTER TABLE  `thread` ADD  `ats` TEXT NULL DEFAULT NULL COMMENT  '@(提)到的人' AFTER  `content`");
        
        if (!$this->isFieldExist('thread_post', 'ats' ))
        $connection->exec("ALTER TABLE  `thread_post` ADD  `ats` TEXT NULL DEFAULT NULL COMMENT  '@(提)到的人' AFTER  `content`");
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