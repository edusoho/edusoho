<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160719164153 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE referer_log ADD  userAgent text COMMENT '浏览器的标识';");
        $this->addSql("ALTER TABLE referer_log MODIFY COLUMN refererUrl VARCHAR(1024) DEFAULT '' COMMENT '访问来源Url';");
        $this->addSql("ALTER TABLE referer_log MODIFY COLUMN refererHost VARCHAR(1024) DEFAULT '' COMMENT '访问来源Url';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
