<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141224130707 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $time = time();
        $this->addSql("
        INSERT INTO `block` (`userId`, `title`, `mode`, `content`, `code`, `createdTime`, `updateTime`) 
        VALUES ('1', '我的账户Banner', 'html', 
        '<br>\n<div class=\"col-md-12\">\n  
        <a href=\"#\"><img src=\"/assets/img/placeholder/banner-wallet.png\" style=\"width: 100%;\"/></a>
        <br>\n<br>\n</div>', 'bill_banner','{$time}','{$time}');
        ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
