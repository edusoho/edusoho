<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141218103432 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `carts` CHANGE `createdTime` `createdTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买时间';");
        $this->addSql("ALTER TABLE `carts` CHANGE `userId` `userId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买者Id';");
    	$this->addSql("ALTER TABLE `carts` CHANGE `number` `number` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '数量';");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
