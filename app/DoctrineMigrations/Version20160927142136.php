<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160927142136 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(" ALTER TABLE `course_member` ADD `lastLearnTime` INT(10) COMMENT '最后学习时间';");

        $this->addSql(" ALTER TABLE `classroom_member` ADD `lastLearnTime` INT(10) COMMENT '最后学习时间';");

        $this->addSql(" ALTER TABLE `classroom_member` ADD `learnedNum` INT(10) COMMENT '已学课时数'; ");

        $this->addSql(" UPDATE `course_member` SET `lastLearnTime` = (SELECT max(startTime) FROM `course_lesson_learn` WHERE course_member.courseId = course_lesson_learn.courseId AND course_member.userId = course_lesson_learn.userId);");

        $this->addSql(" UPDATE `classroom_member` SET `lastLearnTime` = (SELECT max(lastLearnTime) FROM `course_member` WHERE classroom_member.classroomId = course_member.classroomId AND classroom_member.userId = course_member.userId AND course_member.joinedType = 'classroom');");

        $this->addSql(" UPDATE `classroom_member` SET `learnedNum` = (SELECT sum(learnedNum) FROM `course_member` WHERE classroom_member.classroomId = course_member.classroomId AND classroom_member.userId = course_member.userId AND course_member.joinedType = 'classroom');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
