<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130528154348 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `collect` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '收藏的id',
            `courseId` int(10) unsigned NOT NULL COMMENT '收藏课程的Id',
            `userId` int(10) unsigned NOT NULL COMMENT '收藏人的Id',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户的收藏数据表' AUTO_INCREMENT=1 ;"
        );
    }

    public function down(Schema $schema)
    {

    }
}
