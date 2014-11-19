<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141118200828 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("
            CREATE TABLE `edu_material` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `gradeId` int(11) NOT NULL COMMENT '年级',
                `subjectId` int(11) NOT NULL COMMENT '学科',
                `materialId` int(11) NOT NULL COMMENT '教材',
                `materialName` varchar(255) NOT NULL COMMENT '教材名称',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
