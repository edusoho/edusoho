<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150520165734 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        if (!$this->isFieldExist('classroom', 'rating')) {
            $this->addSql("
            ALTER TABLE `classroom` ADD `rating` FLOAT UNSIGNED NOT NULL DEFAULT '0' COMMENT '排行数值' AFTER `postNum`, ADD `ratingNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '投票人数' AFTER `rating`;
          ");
        }

        if (!$this->isFieldExist('classroom', 'categoryId')) {
            $this->addSql("ALTER TABLE `classroom` ADD `categoryId` INT(10) NOT NULL DEFAULT '0' COMMENT '分类id' AFTER `about`;");
        }

        $this->addSql("ALTER TABLE `classroom_member` CHANGE `role` `role` ENUM('auditor','student','teacher','headTeacher','assistant','studentAssistant') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'auditor' COMMENT '角色';");

        if (!$this->isFieldExist('classroom', 'private')) {
            $this->addSql("ALTER TABLE `classroom` ADD COLUMN `private` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否封闭班级';");
        }
        if (!$this->isFieldExist('classroom', 'service')) {
            $this->addSql("ALTER TABLE `classroom` ADD COLUMN `service` varchar(255) DEFAULT NULL COMMENT '班级服务';");
        }

        if (!$this->isFieldExist('classroom_courses', 'disabled')) {
            $this->addSql("ALTER TABLE `classroom_courses` ADD `disabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否禁用' AFTER `courseId`;");
        }

        if (!$this->isFieldExist('classroom', 'noteNum')) {
            $this->addSql("ALTER TABLE `classroom` ADD `noteNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班级笔记数量' AFTER `threadNum`;");
        }
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

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
