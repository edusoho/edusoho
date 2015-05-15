<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150515140331 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `crontab_job` CHANGE `cycle` `cycle` ENUM('once','loop') NOT NULL DEFAULT 'once' COMMENT '任务执行周期的类型，分为执行一次还是多次';");

        $this->addSql("ALTER TABLE `crontab_job` ADD `frequence` INT(10) NOT NULL DEFAULT '0' COMMENT '任务执行频率' AFTER `cycle`;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
