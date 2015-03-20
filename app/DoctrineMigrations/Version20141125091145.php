<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141125091145 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql(
    		"CREATE TABLE IF NOT EXISTS `groups_thread_collect` (
  		`id` int(10) unsigned NOT NULL auto_increment COMMENT 'id主键',
  		`threadId` int(11) unsigned NOT NULL COMMENT '收藏的话题id',
  		`userId` int(10) unsigned NOT NULL COMMENT '收藏人id',
  		`createdTime` int(10) unsigned NOT NULL COMMENT '收藏时间',
  		PRIMARY KEY  (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
