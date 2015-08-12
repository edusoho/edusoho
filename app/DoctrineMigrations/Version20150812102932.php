<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150812102932 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        
        $this->addSql("ALTER TABLE `course` DROP `locked`");
        $this->addSql("ALTER TABLE `course_lesson` DROP `parentId`");
        $this->addSql("ALTER TABLE `question` DROP `pId`");
        $this->addSql("ALTER TABLE `testpaper` DROP `pId`");
        $this->addSql("ALTER TABLE `testpaper_item` DROP `pId`");
        $this->addSql("ALTER TABLE `course_material` DROP `pId`");
        $this->addSql("ALTER TABLE `course_chapter` DROP `pId`");
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course` ADD `locked` INT(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁'");
        $this->addSql("ALTER TABLE `course_lesson` ADD `parentId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id'");
        $this->addSql("ALTER TABLE `question` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制问题对应Id'");
        $this->addSql("ALTER TABLE `testpaper` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id'");
        $this->addSql("ALTER TABLE `testpaper_item` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id'");
        $this->addSql("ALTER TABLE `course_material` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id'");
        $this->addSql("ALTER TABLE `course_chapter` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制章节的id'");  
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
