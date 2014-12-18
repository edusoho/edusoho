<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141217103832 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `testpaper_item` ADD `mistakeScore` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '错选扣分' AFTER `missScore`;");
    	$this->addSql("ALTER TABLE `testpaper_item` ADD `partId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '试卷部分Id' AFTER `questionType`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
