<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160906162455 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `cloud_app` ADD `edusohoMinVersion`  VARCHAR(32) NOT NULL DEFAULT '0.0.0' COMMENT '依赖Edusoho的最小版本';
            ALTER TABLE `cloud_app` ADD `edusohoMaxVersion`  VARCHAR(32) NOT NULL DEFAULT 'up' COMMENT '依赖Edusoho的最大版本';
         ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
