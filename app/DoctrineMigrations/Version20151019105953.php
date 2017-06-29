<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151019105953 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->isTableExist('card')){
            $this->addSql("CREATE TABLE `card` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `cardId` varchar(255)  NOT NULL DEFAULT '' COMMENT '卡的ID',
                  `cardType` varchar(255) NOT NULL DEFAULT '' COMMENT '卡的类型',
                  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间',
                  `useTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
                  `status` enum('normal','invalid','recharged') NOT NULL DEFAULT 'normal' COMMENT '使用状态',
                  `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '领取时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
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
