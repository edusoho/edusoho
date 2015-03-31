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
        $this->addSql("ALTER TABLE `status` ADD `isHidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否隐藏' AFTER `likeNum`;");
        $this->addSql("ALTER TABLE `course_thread` ADD `isHidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否隐藏' AFTER `isClosed`;");
        $this->addSql("ALTER TABLE `course_review` ADD `isHidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否隐藏' AFTER `rating`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
