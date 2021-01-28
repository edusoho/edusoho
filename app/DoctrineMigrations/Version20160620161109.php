<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160620161109 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('referer_log');
        if ($table->hasColumn('sourceUrl')) {
            $this->addSql("ALTER TABLE `referer_log` CHANGE `sourceUrl` `refererUrl` text NOT NULL  COMMENT '访问来源Url';");
        }

        if ($table->hasColumn('sourceHost')) {
            $this->addSql("ALTER TABLE `referer_log` CHANGE `sourceHost` `refererHost` VARCHAR(80)  NOT NULL COMMENT '访问来源HOST';");
        }

        if ($table->hasColumn('sourceName')) {
            $this->addSql("ALTER TABLE `referer_log` CHANGE `sourceName` `refererName` VARCHAR(64)  DEFAULT NUll  COMMENT '访问来源站点名称';");
        }

        if (!$table->hasColumn('updatedTime')) {
            $this->addSql("ALTER TABLE `referer_log` ADD `updatedTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '更新时间' AFTER  `createdTime`;");
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
