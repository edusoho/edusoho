<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151016103829 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        if ($this->isFieldExist('course_lesson', 'parentId')) {
            $this->addSql("ALTER TABLE `course_lesson` CHANGE `parentId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id'");
        }

        if ($this->isFieldExist('question', 'pId')) {
            $this->addSql("ALTER TABLE `question` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制问题对应Id'");
        }

        if ($this->isFieldExist('testpaper', 'pId')) {
            $this->addSql("ALTER TABLE `testpaper` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id'");
        }

        if ($this->isFieldExist('testpaper_item', 'pId')) {
            $this->addSql("ALTER TABLE `testpaper_item` DROP `pId`");
        }

        if ($this->isFieldExist('course_material', 'pId')) {
            $this->addSql("ALTER TABLE `course_material` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id'");
        }

        if ($this->isFieldExist('course_chapter', 'pId')) {
            $this->addSql("ALTER TABLE `course_chapter` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制章节的id'");  
        }

        if($this->isTableExist('homework')) {
            if($this->isFieldExist('homework', 'pId')){
              $this->addSql("ALTER TABLE `homework` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的作业Id'"); 
            }

            if($this->isFieldExist('homework_item', 'pId')){
              $this->addSql("ALTER TABLE `homework_item` DROP `pId`"); 
            }

            if($this->isFieldExist('exercise', 'pId')){
              $this->addSql("ALTER TABLE `exercise` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制练习的Id'"); 
            }

            if($this->isFieldExist('exercise_item', 'pId')){
              $this->addSql("ALTER TABLE `exercise_item` DROP `pId`"); 
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
