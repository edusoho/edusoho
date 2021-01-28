<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160331161730 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `im_conversation` (
                        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `conversationNo` varchar(64) NOT NULL COMMENT 'IM云端返回的会话标识',
                        `userIds` text NOT NULL COMMENT '会话中用户列表(用户id按照小到大排序，竖线隔开)',
                        `createdTime` int(10) UNSIGNED NOT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=`InnoDB` DEFAULT CHARACTER SET utf8 COMMENT='IM云端会话记录表';
                    ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
