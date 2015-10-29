<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151028110310 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `money_card` CHANGE `cardStatus` `cardStatus` ENUM('normal','invalid','recharged','receive') NOT NULL DEFAULT 'invalid';");
        // this up() migration is auto-generated, please modify it to your needs
        if(!$this->isFieldExist('money_card', 'receiveTime')){
            $this->addSql("ALTER TABLE `money_card` ADD `receiveTime` int(10) NOT NULL DEFAULT '0' COMMENT '领取学习卡时间' AFTER `cardStatus`; ");
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
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
