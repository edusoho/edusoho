<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160830113851 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            DROP TABLE IF EXISTS `course_lesson_extend`;
            CREATE TABLE `course_lesson_extend` (
              `id` int(10) NOT NULL COMMENT '课时ID',
              `courseId` int(10) NOT NULL DEFAULT '0' COMMENT '课程ID',
              `doTimes` int(10) NOT NULL DEFAULT '0' COMMENT '可考试次数',
              `redoInterval` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '重做时间间隔(小时)'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷课时扩展表';
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
