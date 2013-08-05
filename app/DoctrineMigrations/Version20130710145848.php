<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130710145848 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("  
            DROP TABLE IF EXISTS `course_quiz`;
            CREATE TABLE IF NOT EXISTS `course_quiz` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `courseId` int(10) unsigned NOT NULL,
              `lessonId` int(10) unsigned NOT NULL,
              `type` enum('lesson','final') NOT NULL DEFAULT 'lesson' COMMENT '测验类型',
              `title` varchar(255) NOT NULL COMMENT '题目',
              `description` text NOT NULL COMMENT '描述',
              `itemIds` varchar(255) NOT NULL COMMENT '对应的题目Id号',
              `userId` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='测验的数据库表，这个是教师添加的' AUTO_INCREMENT=1 ; 
             ");

        $this->addSql("   
            DROP TABLE IF EXISTS `course_quiz_answer`;
            CREATE TABLE IF NOT EXISTS `course_quiz_answer` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `userId` int(11) unsigned NOT NULL,
            `quizId` int(10) unsigned NOT NULL,
            `itemId` int(10) unsigned NOT NULL,
            `answers` varchar(255) NOT NULL COMMENT '测验时用户提供的答案',
            `isCorrect` tinyint(1) NOT NULL COMMENT '测验正确与否结果',
            `createdTime` int(11) NOT NULL COMMENT '创建时间',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用于记录用户答题的数据库表，对应与item的内容' AUTO_INCREMENT=1 ;
            ");

        $this->addSql("   
            DROP TABLE IF EXISTS `course_quiz_item`;
            CREATE TABLE IF NOT EXISTS `course_quiz_item` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `courseId` int(10) unsigned NOT NULL,
              `lessonId` int(10) unsigned NOT NULL,
              `description` text NOT NULL COMMENT '题目描述',
              `level` enum('low','normal','high') NOT NULL COMMENT '难度等级',
              `type` enum('single','multiple') NOT NULL COMMENT '问答题目类型',
              `choices` text NOT NULL COMMENT '课时测验的可选结果',
              `answers` varchar(255) NOT NULL COMMENT '答案可以多个，用|竖线分割',
              `createdTime` int(11) unsigned NOT NULL COMMENT '课时测验创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='单个测验题目所对应的数据库表' AUTO_INCREMENT=1 ;
            ");
        $this->addSql("  
            DROP TABLE IF EXISTS `course_quiz_used`;
            CREATE TABLE IF NOT EXISTS `course_quiz_used` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `quizId` int(11) NOT NULL COMMENT 'quizId',
              `courseId` int(10) unsigned NOT NULL,
              `lessonId` int(10) unsigned NOT NULL,
              `itemIds` varchar(255) NOT NULL COMMENT '对应的题目Id号',
              `score` tinyint(2) NOT NULL DEFAULT '0' COMMENT '测验分数',
              `userId` int(11) unsigned NOT NULL,
              `startTime` int(11) unsigned NOT NULL COMMENT '开始时间',
              `endTime` int(11) unsigned NOT NULL COMMENT '结束时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='测验的数据库表，由用户自动随即生成，并且与用户绑定。' AUTO_INCREMENT=1 ;
            ");
    
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
