<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140816115158 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `course_lesson` ADD `replayStatus` ENUM('ungenerated','generating','generated') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ungenerated' AFTER `memberNum`;");
        $this->addSql("
            CREATE TABLE `course_lesson_replay` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `lessonId` int(10) unsigned NOT NULL COMMENT '所属课时',
              `courseId` int(10) unsigned NOT NULL COMMENT '所属课程',
              `title` varchar(255) NOT NULL COMMENT '名称',
              `replayId` text NOT NULL COMMENT '云直播中的回放id',
              `userId` int(10) unsigned NOT NULL COMMENT '创建者',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    	");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
