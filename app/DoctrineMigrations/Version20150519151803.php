<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150519151803 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->isFieldExist('classroom', 'recommended')) {
          $this->addSql("ALTER TABLE classroom ADD COLUMN `recommended` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐班级';");
        }

        if (!$this->isFieldExist('classroom', 'recommendedSeq')) {
          $this->addSql("ALTER TABLE classroom ADD COLUMN `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '推荐序号';");
        }

        if (!$this->isFieldExist('classroom', 'recommendedTime')) {
          $this->addSql("ALTER TABLE classroom ADD COLUMN `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间';");
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
