<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150618194042 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->isTableExist('blacklist')) {
            $this->addSql("
                CREATE TABLE `blacklist` (
                 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
                 `userId` int(10) unsigned NOT NULL COMMENT '名单拥有者id',
                 `blackId` int(10) unsigned NOT NULL COMMENT '黑名单用户id',
                 `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入黑名单时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';
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
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
