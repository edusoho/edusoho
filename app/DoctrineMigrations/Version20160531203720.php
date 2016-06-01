<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Topxia\Service\Common\ServiceKernel;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160531203720 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql     = "select * from course_lesson where type not in('live','testpaper') and mediaId != 0";
        $lessons = $this->connection->fetchAll($sql, array());

        if ($lessons) {
            foreach ($lessons as $key => $lesson) {
                $courseId = $lesson['courseId'];
                $lessonId = $lesson['id'];
                $fileId   = $lesson['mediaId'];
                $copyId   = 0;
                $userId   = $lesson['userId'];
                $time     = time();

                $this->addSql("insert into course_material (courseId,lessonId,title,fileId,source,copyId,userId,createdTime) values({$courseId},{$lessonId},'',{$fileId},'courselesson',{$copyId},{$userId},UNIX_TIMESTAMP());");
            }
        }

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
