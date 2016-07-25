<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160322173743 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isTableExist('open_course_recommend')) {
            $this->addSql("
                CREATE TABLE `open_course_recommend` (
                 `id` int(10) NOT NULL AUTO_INCREMENT,
                 `openCourseId` int(10) NOT NULL COMMENT '公开课id',
                 `recommendCourseId` int(10) NOT NULL DEFAULT '0' COMMENT '推荐课程id',
                 `seq` int(10) NOT NULL DEFAULT '0' COMMENT '序列',
                 `type` varchar(255) NOT NULL COMMENT '类型',
                 `createdTime` int(10) NOT NULL COMMENT '创建时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公开课推荐课程表'
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
