<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160314134334 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        if (!$this->isTableExist('upload_files_tag')) {
            $this->addSql("
            CREATE TABLE `upload_files_tag` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统ID',
            `fileId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件ID',
            `tagId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件与标签的关联表'
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

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
