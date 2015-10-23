<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141106130441 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `course` ADD `deadlineNotify` ENUM('active','none') NOT NULL DEFAULT 'none' COMMENT '开启有效期通知' AFTER `userId`, ADD `daysOfNotifyBeforeDeadline` INT(10) NOT NULL DEFAULT '0' AFTER `deadlineNotify`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
