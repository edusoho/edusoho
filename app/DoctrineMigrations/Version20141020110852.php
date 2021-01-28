<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141020110852 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("
    		ALTER TABLE `course_lesson` ADD `liveProvider` int(10) unsigned NOT NULL DEFAULT 0 AFTER `replayStatus`;
    		UPDATE `course_lesson` set `liveProvider` = 1 where `type` = 'live';
    	");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
