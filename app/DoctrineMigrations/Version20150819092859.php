<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150819092859 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->isFieldExist('course', 'maxRate')) {
            $this->addSql("ALTER TABLE `course` ADD `maxRate` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比';");
        }

        if (!$this->isFieldExist('classroom', 'maxRate')) {
            $this->addSql("ALTER TABLE `classroom` ADD `maxRate` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比';");
        }

        if (($this->isTableExist('vip_level'))&&(!$this->isFieldExist('vip_level', 'maxRate'))) {
            $this->addSql("ALTER TABLE `vip_level` ADD `maxRate` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比';");
        }

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
