<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160304091812 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isFieldExist('course', 'studyModel')) {
            $this->addSql("ALTER TABLE `course` ADD `studyModel` ENUM( 'normal', 'ordered' ) NOT NULL DEFAULT 'normal' COMMENT '学习模式';");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
