<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130604161152 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            ALTER TABLE `course` ADD  `goals` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '课程目标' AFTER  `about` ,
            ADD  `SCG` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'Suitable Consumer Groups的缩写，适应人群' AFTER  `goals`;
            ");
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
