<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150819091720 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isFieldExist('classroom', 'showable')) {
            $this->addsql("ALTER TABLE classroom ADD showable tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示';");
        }
        if (!$this->isFieldExist('classroom', 'buyable')) {
        $this->addsql("ALTER TABLE classroom ADD buyable tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放购买';");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        
    }
    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
