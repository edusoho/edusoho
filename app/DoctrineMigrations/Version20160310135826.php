<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160310135826 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `dictionary` (
                     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                     `type` varchar(255) NOT NULL,
                     `code` varchar(64) DEFAULT NULL,
                     `name` varchar(255) NOT NULL,
                     `weight` int(11) NOT NULL DEFAULT '0',
                     `createdTime` int(10) unsigned NOT NULL,
                     `updateTime` int(10) unsigned DEFAULT '0',
                     PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8
                    ");

        $this->addSql("INSERT INTO `dictionary` (`id`, `type`, `code`, `name`, `weight`, `createdTime`, `updateTime`) VALUES ('1', 'quitReason', NULL, '课程内容质量差', '0', '0', '0');");

        $this->addSql("INSERT INTO `dictionary` (`id`, `type`, `code`, `name`, `weight`, `createdTime`, `updateTime`) VALUES ('2', 'quitReason', NULL, '老师服务态度不好', '0', '0', '0');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
