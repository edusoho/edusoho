<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150123171718 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `classroom_member` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `classId` int(10) unsigned NOT NULL DEFAULT '0',
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `orderId` int(10) unsigned NOT NULL DEFAULT '0',
              `levelId` int(10) unsigned NOT NULL DEFAULT '0',
              `noteNum` int(10) unsigned NOT NULL DEFAULT '0',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
              `noteLastUpdateTime` int(10) unsigned NOT NULL DEFAULT '0',
              `remark` text COMMENT '备注',
              `role` enum('aduitor','student','teacher') NOT NULL DEFAULT 'aduitor',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
