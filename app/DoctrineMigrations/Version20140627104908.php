<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140627104908 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("DROP TABLE IF EXISTS `homework`;
							CREATE TABLE IF NOT EXISTS `homework` (
							  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
							  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
							  `lessonId` int(10) unsigned NOT NULL DEFAULT '0',
							  `description` text COMMENT '作业说明',
							  `completeLimit` enum('inherited','yes','no') NOT NULL DEFAULT 'inherited' COMMENT '作业限制是否必须完成',
							  `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
							  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
							  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
							  `updatedUserId` int(10) unsigned NOT NULL,
							  `updatedTime` int(10) unsigned NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='作业';

    				   DROP TABLE IF EXISTS `homework_item`;
							CREATE TABLE IF NOT EXISTS `homework_item` (
							  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
							  `homeworkId` int(11) NOT NULL DEFAULT '0' COMMENT '所属作业',
							  `seq` int(11) NOT NULL COMMENT '题目顺序',
							  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目ID',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    					
					   DROP TABLE IF EXISTS `homework_item_result`;
							CREATE TABLE IF NOT EXISTS `homework_item_result` (
							  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
							  `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '作业题目ID',
							  `homeworkId` int(10) unsigned NOT NULL DEFAULT '0',
							  `homeworkResultId` int(10) unsigned NOT NULL,
							  `questionId` int(10) unsigned NOT NULL,
							  `userId` int(10) unsigned NOT NULL DEFAULT '0',
							  `status` enum('none','right','partRight','wrong','noAnswer') DEFAULT 'none',
							  `answer` text,
							  `teacherSay` text,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;

						
						DROP TABLE IF EXISTS `homework_result`;
							CREATE TABLE IF NOT EXISTS `homework_result` (
							  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
							  `homeworkId` int(10) unsigned NOT NULL DEFAULT '0',
							  `courseId` int(10) unsigned NOT NULL,
							  `lessonId` int(10) unsigned NOT NULL,
							  `userId` int(10) unsigned NOT NULL DEFAULT '0',
							  `teacherSay` text,
							  `rightItemCount` int(10) unsigned NOT NULL DEFAULT '0',
							  `status` enum('doing','reviewing','finished') NOT NULL COMMENT '状态',
							  `checkTeacherId` int(10) unsigned NOT NULL DEFAULT '0',
							  `checkedTime` int(11) NOT NULL DEFAULT '0',
							  `usedTime` int(10) unsigned NOT NULL DEFAULT '0',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

						DROP TABLE IF EXISTS `exercise`;
							CREATE TABLE IF NOT EXISTS `exercise` (
							  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
							  `itemCount` int(11) NOT NULL COMMENT '题目数量',
							  `source` enum('course','lesson') NOT NULL,
							  `courseId` int(10) unsigned NOT NULL,
							  `lessonId` int(10) unsigned NOT NULL,
							  `difficulty` varchar(64) NOT NULL DEFAULT '''''' COMMENT '难度',
							  `questionTypeRange` varchar(255) NOT NULL DEFAULT '' COMMENT '题型范围',
							  `createdUserId` int(10) unsigned NOT NULL,
							  `createdTime` int(11) NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;

						DROP TABLE IF EXISTS `exercise_item`;
							CREATE TABLE IF NOT EXISTS `exercise_item` (
							  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
							  `exerciseId` int(10) unsigned NOT NULL DEFAULT '0',
							  `seq` int(10) unsigned NOT NULL,
							  `questionId` int(10) unsigned NOT NULL,
							  `questionType` varchar(64) NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;

						DROP TABLE IF EXISTS `exercise_result`;
							CREATE TABLE IF NOT EXISTS `exercise_result` (
							  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
							  `exerciseId` int(10) unsigned NOT NULL,
							  `userId` int(11) NOT NULL COMMENT '做练习的学生ID',
							  `target` varchar(255) NOT NULL DEFAULT '',
							  `status` enum('doing','finished') NOT NULL COMMENT '状态',
							  `usedTime` int(11) NOT NULL COMMENT '做练习的时间',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='做练习结果';
    					"


    	);
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
