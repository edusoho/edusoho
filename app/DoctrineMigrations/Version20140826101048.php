<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140826101048 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE `class` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `gradeId` int(10) NOT NULL DEFAULT '0' COMMENT '年级ID',
            `year` int(10) unsigned NOT NULL COMMENT '年份',
            `term` enum('first','second') NOT NULL DEFAULT 'first' COMMENT '学期',
            `headTeacherId` int(10) NOT NULL,
            `enabled` tinyint(3) NOT NULL DEFAULT '1',
            `icon` varchar(64) DEFAULT NULL COMMENT '图标',
            `backgroudImg` varchar(255) DEFAULT '',
            `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学生数',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `class_member` (
            `id` int(10) unsigned NOT NULL,
            `classId` int(10) unsigned NOT NULL COMMENT '班级ID',
            `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
            `role` varchar(64) NOT NULL,
            `title` varchar(255) NOT NULL DEFAULT '',
            `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            ALTER TABLE  `user` ADD  `firstLogin` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '第一次登录' AFTER  `loginSessionId`;
            ALTER TABLE  `user` ADD  `number` VARCHAR( 32 ) NOT NULL COMMENT  '学号/工号' AFTER  `id`;
            ALTER TABLE  `course` ADD  `classId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '班级ID' AFTER  `id`;
            ALTER TABLE  `course` ADD  `gradeId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '年级ID' AFTER  `classId`;
            ALTER TABLE  `course` ADD  `term` ENUM(  'first',  'second' ) NOT NULL DEFAULT  'first' COMMENT  '学期' AFTER  `gradeId`;

            ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
