<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160511130729 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($this->isFieldExist('im_conversation', 'conversationNo')) {
            $this->addSql("ALTER TABLE `im_conversation` CHANGE COLUMN `conversationNo` `no` varchar(64) NOT NULL COMMENT 'IM云端返回的会话id';");
        }

        if ($this->isFieldExist('im_conversation', 'userIds')) {
            $this->addSql("ALTER TABLE `im_conversation` CHANGE COLUMN `userIds` `memberIds` text NOT NULL COMMENT '会话中用户列表(用户id按照小到大排序，竖线隔开)';");
        }

        if (!$this->isFieldExist('im_conversation', 'memberHash')) {
            $this->addSql("ALTER TABLE `im_conversation` ADD `memberHash` varchar(32) NOT NULL DEFAULT '' COMMENT 'memberIds字段的hash值，用于优化查询' AFTER `memberIds`;");
        }

        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `im_my_conversation` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `no` varchar(64) NOT NULL,
                `userId` int(10) UNSIGNED NOT NULL,
                `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='用户个人的会话列表';"
        );
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
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
