<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151217172230 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isFieldExist('task', 'intervalDate')) {
            $this->addSql("
                ALTER TABLE `task` ADD `intervalDate` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '历时天数' AFTER `taskEndTime`;
            ");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
