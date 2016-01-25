<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151105115345 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isFieldExist('course_lesson', 'testMode')) {
            $this->addSql("ALTER TABLE `course_lesson` ADD `testMode` ENUM('normal', 'realTime') NULL DEFAULT 'normal' COMMENT '考试模式'");
        }

        if (!$this->isFieldExist('course_lesson', 'testStartTime')) {
            $this->addSql("ALTER TABLE `course_lesson` ADD `testStartTime` INT(10) NULL DEFAULT '0' COMMENT '实时考试开始时间'");
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
}
