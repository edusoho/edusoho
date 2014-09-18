<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140917171726 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE `class_member_sign` (
				`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Id',
				`classId` int(10) NOT NULL COMMENT '班级Id',
				`userId` int(10) NOT NULL COMMENT '用户Id',
				`createdTime` int(10) NOT NULL COMMENT '签到时间',
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->addSql("CREATE TABLE `class_member_sign_statistics` (
				`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
				`classId` int(10) NOT NULL COMMENT '班级Id',
				`userId` int(10) NOT NULL COMMENT '用户Id',
				`todayRank` int(10) NOT NULL DEFAULT '0' COMMENT '今日排名',
				`keepDays` int(10) NOT NULL DEFAULT '0' COMMENT '连续签到天数',
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->addSql("CREATE TABLE `class_sign_statistics` (
				`id` int(10) NOT NULL AUTO_INCREMENT,
				`classId` int(10) NOT NULL,
				`date` int(10) NOT NULL DEFAULT '0',
				`signedNum` int(10) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
