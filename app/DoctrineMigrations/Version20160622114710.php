<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160622114710 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('referer_log');
        if ($table->hasColumn('targertId')) {
            $this->addSql(" ALTER TABLE `referer_log` CHANGE `targertId` `targetId` VARCHAR(64)  DEFAULT NUll  COMMENT '访问来源站点名称';");
        }
        $table = $schema->getTable('referer_log');
        if ($table->hasColumn('targertType')) {
            $this->addSql("ALTER TABLE `referer_log` CHANGE `targertType` `targetType` VARCHAR(64)  DEFAULT NUll  COMMENT '访问来源站点名称';");
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
