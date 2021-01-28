<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150604233338 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE `block` SET `code` = 'default-b:home_top_banner' WHERE `code` = 'home_top_banner';");
        $this->addSql("INSERT INTO `block`( `userId`, `title`, `mode`, `template`, `templateName`, `templateData`, `content`, `code`, `meta`, `data`, `tips`, `createdTime`, `updateTime`, `category`) select `userId`, `title`, `mode`, `template`, `templateName`, `templateData`, `content`, 'default-c:home_top_banner', `meta`, `data`, `tips`, `createdTime`, `updateTime`, `category` from `block` where `code`='default-b:home_top_banner';");
        $this->addSql("UPDATE `block` SET templateName='@theme/default-c/block/home_top_banner.template.html.twig' where code='default-c:home_top_banner';");
        $this->addSql("UPDATE `block` SET templateName='@theme/default-b/block/home_top_banner.template.html.twig' where code='default-b:home_top_banner';");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
