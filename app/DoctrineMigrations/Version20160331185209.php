<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160331185209 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->isTableExist('upload_files_share_history')) {
            $this->addSql("
            CREATE TABLE `upload_files_share_history` (
                         `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统ID',
                         `sourceUserId` int(10) NOT NULL COMMENT '分享用户的ID',
                         `targetUserId` int(10) NOT NULL COMMENT '被分享的用户的ID',
                         `isActive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '',
                         `createdTime` int(10) DEFAULT '0' COMMENT '创建时间',
                         PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
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
