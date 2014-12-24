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
        $this->addSql("
        INSERT INTO `block` (`userId`, `title`, `mode`, `content`, `code`) 
        VALUES ('1', '我的账户Bar', 'html', 
        '<br>\n<div class=\"col-md-12\">\n  
        <a href=\"#\"><img src=\"http://open.edusoho.com/assets/img/edusoho-demo/vip-banner-1.jpg\" /></a>
        <br>\n<br>\n</div>', 'bill_bar');
        ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
