<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140813202042 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `testpaper` ADD  `passedScore` FLOAT( 10, 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '通过考试的分数线' AFTER  `score`");
        $this->addSql("ALTER TABLE  `testpaper_result` ADD  `passedStatus` ENUM(  'none',  'passed',  'unpassed' ) NOT NULL DEFAULT  'none' COMMENT  '考试通过状态，none表示该考试没有' AFTER  `rightItemCount`");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
