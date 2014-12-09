<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141205150132 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `lecture_note` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `courseId` int(11) NOT NULL COMMENT '课程Id',
              `lessonId` int(11) NOT NULL COMMENT '课时Id',
              `title` varchar(128) NOT NULL COMMENT '标题',
              `essayId` int(11) NOT NULL COMMENT '文章Id',
              `essayMaterialId` int(11) NOT NULL COMMENT '文章素材Id',
              `userId` int(11) NOT NULL COMMENT '创建者Id',
              `createdTime` int(11) NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
