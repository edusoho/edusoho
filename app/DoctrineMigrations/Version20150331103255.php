<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150331103255 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `status` ADD `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否私有' AFTER `likeNum`;");
        $this->addSql("ALTER TABLE `course_thread` ADD `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否私有' AFTER `isClosed`;");
        $this->addSql("ALTER TABLE `course_review` ADD `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否私有' AFTER `rating`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
