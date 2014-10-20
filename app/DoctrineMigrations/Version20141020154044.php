<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141020154044 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
		$this->addSql(
			"CREATE TABLE IF NOT EXISTS `exercise_item` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `exerciseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属练习',
			  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
			  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目ID',
			  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0',
			  `missScore` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '漏选得分',
			  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;"
        );

        $this->addSql("
			CREATE TABLE IF NOT EXISTS `exercise_item_result` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '练习题目ID',
			  `exerciseId` int(10) unsigned NOT NULL DEFAULT '0',
			  `questionId` int(10) unsigned NOT NULL,
			  `userId` int(10) unsigned NOT NULL DEFAULT '0',
			  `status` enum('none','right','partRight','wrong','noAnswer') DEFAULT 'none',
			  `answer` text,
			  `teacherSay` text,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
