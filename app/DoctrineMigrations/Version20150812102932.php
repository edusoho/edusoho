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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course` ADD `locked` INT(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁'");
        $this->addSql("ALTER TABLE `course_lesson` ADD `parentId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id'");
        $this->addSql("ALTER TABLE `question` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制问题对应Id'");
        $this->addSql("ALTER TABLE `testpaper` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id'");
        $this->addSql("ALTER TABLE `testpaper_item` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id'");
        $this->addSql("ALTER TABLE `course_material` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id'");
        $this->addSql("ALTER TABLE `course_chapter` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制章节的id'");
        
        if($this->isTableExist('homework')) {
            $this->addSql("ALTER TABLE `homework` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的作业Id'");
            $this->addSql("ALTER TABLE `homework_item` ADD `pId`INT(10) NOT NULL DEFAULT '0' COMMENT '复制练习问题ID'");
        }
        
        if($this->isTableExist('exercise')){
            $this->addSql("ALTER TABLE `exercise` ADD `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制练习的ID'");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
