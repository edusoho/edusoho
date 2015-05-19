<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150415103250 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_announcement` ADD COLUMN `targetType` varchar(64) NOT NULL DEFAULT 'course' COMMENT '公告类型' AFTER `userId`");
        $this->addSql("ALTER TABLE `course_announcement` ADD COLUMN `url` varchar(255) NOT NULL AFTER `targetType`");
        $this->addSql("ALTER TABLE `course_announcement` ADD COLUMN `startTime` int(10) unsigned NOT NULL DEFAULT '0' AFTER `url`");
        $this->addSql("ALTER TABLE `course_announcement` ADD COLUMN `endTime` int(10) unsigned NOT NULL DEFAULT '0' AFTER `startTime`");
        $this->addSql("ALTER TABLE `course_announcement` CHANGE `courseId` `targetId`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告类型ID'");

        if($this->isTableExist("announcement")) {
            $this->addSql("insert into `course_announcement` (content, url, startTime, endTime, userId, targetId, targetType, createdTime) select title, url, startTime, endTime, userId, 0, 'global', 0 from announcement");
            $this->addSql("drop TABLE `announcement`;");
        }


        $this->addSql("ALTER TABLE `course_announcement` RENAME TO `announcement`");
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
