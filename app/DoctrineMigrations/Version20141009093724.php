<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141009093724 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE `schedule` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
 `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时id',
 `classId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级id',
 `date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时日期',
 `sequence` int(3) unsigned NOT NULL DEFAULT '1' COMMENT '课时顺序',
 `createdBy` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人id',
 `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=283 DEFAULT CHARSET=utf8");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
