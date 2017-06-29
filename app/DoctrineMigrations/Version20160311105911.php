<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160311105911 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        if (!$this->isTableExist('upload_files_collection')) {
            $this->addSql("
            CREATE TABLE `upload_files_collection` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `fileId` int(10) unsigned NOT NULL COMMENT '文件Id',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏者',
              `createdTime` int(10) unsigned NOT NULL,
              `updatedTime` INT(10) unsigned NULL DEFAULT '0'  COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文件收藏表';
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
