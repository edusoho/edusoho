<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160701142706 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('referer_log');
        if (!$table->hasColumn('token')) {
            $this->addSql("ALTER TABLE referer_log ADD token VARCHAR(64) DEFAULT NULL  COMMENT '当前访问的token值';");
        }

        if (!$table->hasColumn('ip')) {
            $this->addSql("ALTER TABLE referer_log ADD ip VARCHAR(64) DEFAULT NULL  COMMENT '访问者IP';");
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
