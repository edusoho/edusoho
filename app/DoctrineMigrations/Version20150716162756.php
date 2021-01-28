<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150716162756 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addsql("ALTER TABLE `classroom_member` CHANGE `role` `role` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'auditor' COMMENT '角色'");
    	if (!$this->isFieldExist('classroom', 'assistantIds')) {
            $this->addsql("ALTER TABLE `classroom` ADD `assistantIds` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '助教Ids' AFTER `teacherIds`");
        }
        $this->addsql("UPDATE `classroom_member` SET `role` = concat('|',role,'|') WHERE `role` NOT LIKE '%|%'");
        $this->addsql("UPDATE `classroom_member` SET `role` = '|assistant|student|' WHERE `role` = '|studentAssistant|'");
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
}
