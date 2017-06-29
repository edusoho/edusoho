<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160323191830 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isTableExist('open_course')) {
            $this->addSql("
                CREATE TABLE `open_course` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程ID',
                  `title` varchar(1024) NOT NULL COMMENT '课程标题',
                  `subtitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '课程副标题',
                  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft' COMMENT '课程状态',
                  `type` varchar(255) NOT NULL DEFAULT 'normal' COMMENT '课程类型',
                  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
                  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
                  `tags` text COMMENT '标签IDs',
                  `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
                  `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
                  `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
                  `about` text COMMENT '简介',
                  `teacherIds` text COMMENT '显示的课程教师IDs',
                  `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
                  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
                  `likeNum` int(10) NOT NULL DEFAULT '0' COMMENT '点赞数',
                  `postNum` int(10) NOT NULL DEFAULT '0' COMMENT '评论数',
                  `userId` int(10) unsigned NOT NULL COMMENT '课程发布人ID',
                  `parentId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程的父Id',
                  `locked` INT(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁',
                  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
                  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
                  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '课程创建时间',
                  `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

            ");
            $this->addSql("ALTER TABLE `open_course` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if (!$this->isTableExist('open_course_lesson')) {
            $this->addSql("
                CREATE TABLE `open_course_lesson` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课时ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '课时所属课程ID',
                  `chapterId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时所属章节ID',
                  `number` int(10) unsigned NOT NULL COMMENT '课时编号',
                  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时在课程中的序号',
                  `free` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为免费课时',
                  `status` enum('unpublished','published') NOT NULL DEFAULT 'published' COMMENT '课时状态',
                  `title` varchar(255) NOT NULL COMMENT '课时标题',
                  `summary` text COMMENT '课时摘要',
                  `tags` text COMMENT '课时标签',
                  `type` varchar(64) NOT NULL DEFAULT 'text' COMMENT '课时类型',
                  `content` text COMMENT '课时正文',
                  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课时获得的学分',
                  `requireCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习课时前，需达到的学分',
                  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
                  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
                  `mediaName` varchar(255) NOT NULL DEFAULT '' COMMENT '媒体文件名称',
                  `mediaUri` text COMMENT '媒体文件资源名',
                  `homeworkId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '作业iD',
                  `exerciseId` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT '练习ID',
                  `length` int(11) unsigned DEFAULT NULL COMMENT '时长',
                  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
                  `quizNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '测验题目数量',
                  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学的学员数',
                  `viewedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看数',
                  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时开始时间',
                  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时结束时间',
                  `memberNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时加入人数',
                  `replayStatus` enum('ungenerated','generating','generated') NOT NULL DEFAULT 'ungenerated',
                  `maxOnlineNum` INT NULL DEFAULT '0' COMMENT '直播在线人数峰值',
                  `liveProvider` int(10) unsigned NOT NULL DEFAULT '0',
                  `userId` int(10) unsigned NOT NULL COMMENT '发布人ID',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id',
                  `suggestHours` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '建议学习时长',
                  `testMode` ENUM('normal', 'realTime') NULL DEFAULT 'normal' COMMENT '考试模式',
                  `testStartTime` INT(10) NULL DEFAULT '0' COMMENT '实时考试开始时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
            $this->addSql("ALTER TABLE `open_course_lesson` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if (!$this->isTableExist('open_course_member')) {
            $this->addSql("
                CREATE TABLE `open_course_member` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程学员记录ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员ID',
                  `mobile` varchar(32) NOT NULL DEFAULT  '' COMMENT '手机号码',
                  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学课时数',
                  `learnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习时间',
                  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
                  `isVisible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '可见与否，默认为可见',
                  `role` enum('student','teacher') NOT NULL DEFAULT 'student' COMMENT '课程会员角色',
                  `ip` varchar(64) COMMENT 'IP地址',
                  `lastEnterTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次进入时间',
                  `isNotified` int(10) NOT NULL DEFAULT '0' COMMENT '直播开始通知',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '学员加入课程时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('course_favorite', 'type')) {
            $this->addSql("ALTER TABLE course_favorite ADD `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型';");
        }

        if (!$this->isFieldExist('course_lesson_replay', 'type')) {
            $this->addSql("ALTER TABLE course_lesson_replay ADD `type` varchar(50) NOT NULL DEFAULT 'live' COMMENT '课程类型';");
        }

        if (!$this->isFieldExist('course_material', 'type')) {
            $this->addSql("ALTER TABLE course_material ADD `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型';");
        }
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
