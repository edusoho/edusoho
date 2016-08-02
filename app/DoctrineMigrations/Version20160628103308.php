<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160628103308 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('referer_log');
        if ($table->hasColumn('targertId') && !$table->hasColumn('targetInnerType')) {
            $this->addSql("ALTER TABLE referer_log ADD targetInnerType VARCHAR(64) NULL;");
            $this->addSql("ALTER TABLE referer_log MODIFY COLUMN targetInnerType VARCHAR(64) COMMENT '模块自身的类型' AFTER targetId;");
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
