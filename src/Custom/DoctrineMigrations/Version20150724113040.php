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
    {//periods
        $this->addSql("ALTER TABLE `course` ADD `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间';");
        $this->addSql("ALTER TABLE `course` ADD `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间';");
        $this->addSql("ALTER TABLE `course` ADD `rootId` int(10) unsigned DEFAULT '0' COMMENT '根课程ID';");
        $this->addSql("ALTER TABLE `course` ADD `periods` int unsigned NOT NULL DEFAULT '1' COMMENT '周期课程的期数';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE `course` DROP `startTime`;");
        $this->addSql("ALTER TABLE `course` DROP `endTime`;");
        $this->addSql("ALTER TABLE `course` DROP `rootId`;");
        $this->addSql("ALTER TABLE `course` DROP `periods`;");
    }
}
