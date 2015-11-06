<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151030162237 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
          $this->addSql("
           ALTER TABLE `course_score_setting` CHANGE `credit` `credit` FLOAT(10,1) UNSIGNED COMMENT '可获得学分';
           ALTER TABLE `course_score_setting` CHANGE `otherWeight` `otherWeight` INT(10) UNSIGNED NULL COMMENT '其它分权重';
           ");
        // this up() migration is auto-generated, please modify it to your needs
      
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
