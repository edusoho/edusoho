<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150724113040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `course` ADD `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间';");
        $this->addSql("ALTER TABLE `course` ADD `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE `course` DROP `startTime`;");
        $this->addSql("ALTER TABLE `course` DROP `endTime`;");
    }
}
