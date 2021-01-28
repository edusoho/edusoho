<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

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
        $sql     = "select * from course_lesson where type not in('text','live','testpaper') and mediaId != 0 and mediaSource = 'self';";
        $lessons = $this->connection->fetchAll($sql, array());

        if ($lessons) {
            foreach ($lessons as $key => $lesson) {
                $courseSql = "select id,parentId from course where id=".$lesson['courseId'];
                $course    = $this->connection->fetchAssoc($courseSql);
                
                if (!$course) {
                    continue;
                }

                $courseSql = "select id,parentId from course where id=".$lesson['courseId'];
                $course    = $this->connection->fetchAssoc($courseSql);

                if (!$course) {
                    continue;
                }

                $sql  = "select id,filename,fileSize from upload_files where id=".$lesson['mediaId'];
                $file = $this->connection->fetchAssoc($sql);

                $materialSql    = "select id from course_material where lessonId=".$lesson['copyId']." and fileId=".$lesson['mediaId']." and source='courselesson';";
                $parentMaterial = $this->connection->fetchAssoc($materialSql);

                if ($file) {
                    $courseId = $lesson['courseId'];
                    $lessonId = $lesson['id'];
                    $title    = $file['filename'];
                    $fileId   = $file['id'];
                    $fileSize = $file['fileSize'];
                    $copyId   = $parentMaterial ? $parentMaterial['id'] : 0;
                    $userId   = $lesson['userId'];
                    $time     = time();

                    $this->addSql("insert into course_material (courseId,lessonId,title,fileId,fileSize,source,copyId,userId,createdTime) values({$courseId},{$lessonId},'{$title}',{$fileId},{$fileSize},'courselesson',{$copyId},{$userId},UNIX_TIMESTAMP());");
                }
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
