<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150519141733 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
        CREATE TABLE IF NOT EXISTS `classroom` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL COMMENT '标题',
            `status` enum('closed','draft','published') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布',
            `about` text COMMENT '简介',
            `categoryId` int(10) NOT NULL DEFAULT '0' COMMENT '分类id',
            `description` text COMMENT '课程说明',
            `price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
            `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支持的vip等级',
            `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
            `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
            `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
            `headTeacherId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班主任ID',
            `teacherIds` varchar(255) NOT NULL DEFAULT '' COMMENT '教师IDs',
            `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
            `auditorNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '旁听生数',
            `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
            `courseNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程数',
            `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
            `threadNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题数',
            `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级笔记数量',
            `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
            `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '收入',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `service` varchar(255) DEFAULT NULL COMMENT '班级服务',
            `private` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否封闭班级',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `classroom_courses` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `classroomId` int(10) unsigned NOT NULL COMMENT '班级ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `classroom_member` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
              `levelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
              `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记数',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题数',
              `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '学员是否被锁定',
              `remark` text COMMENT '备注',
              `role` enum('auditor','student','teacher','headTeacher','assistant', 'studentAssistant') NOT NULL DEFAULT 'auditor' COMMENT '角色',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `classroom_review` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
              `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
              `content` text NOT NULL COMMENT '内容',
              `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分0-5',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
