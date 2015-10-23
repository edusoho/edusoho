<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140822142549 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        
        $this->addSql("
               ALTER TABLE `groups_thread_post` ADD `postId` INT(10) UNSIGNED NULL AFTER `userId`;
               ALTER TABLE `groups_thread_post` CHANGE `postId` `postId` INT(10) UNSIGNED NULL DEFAULT '0';
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
