<?php

class CourseMaterial2DownloadActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->migrateTableStructure();

        //create table course_material_v8 and dumplcate date from course_material
        
        $this->dumplicateCourseMaterialDatas();

        $this->exec(' UPDATE `course_material_v8` SET `courseSetId` = courseId WHERE `courseSetId`<>`courseId`;');
        $this->exec(" UPDATE `course_material_v8` SET  `source`= 'courseactivity' WHERE source= 'courselesson';");

        $this->proccessDownloadActivity();
    }

    protected function migrateTableStructure()
    {
        if (!$this->isTableExist('download_activity')) {
            $this->exec(
                "
              CREATE TABLE `download_activity` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                `mediaCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料数',
                `createdTime` int(10) unsigned NOT NULL,
                `updatedTime` int(10) unsigned NOT NULL,
                `fileIds` varchar(1024) DEFAULT NULL COMMENT '下载资料Ids',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
              "
            );
        }

        if (!$this->isFieldExist('download_activity', 'migrateLessonId')) {
            $this->exec('alter table `download_activity` add `migrateLessonId` int(10) ;');
        }

        if (!$this->isFieldExist('course_material', 'courseSetId')) {
            $this->exec('alter table `course_material` add `courseSetId` int(10) ;');
        }
    }

    protected function dumplicateCourseMaterialDatas()
    {
        if (!$this->isTableExist("course_material_v8")) {
            $this->exec('CREATE TABLE course_material_v8 AS SELECT * FROM course_material;');
        }
    }

    protected function proccessDownloadActivity()
    {
        $this->exec(
          "
          INSERT INTO `download_activity`
          (
            `mediaCount`,
            `createdTime`,
            `updatedTime`,
            `fileIds`,
            `migrateLessonId`
          )
          SELECT
            count(lessonId) AS mediaCount,
            min(`createdTime`) AS createdTime,
            min(`createdTime`) AS updatedTime,
            concat('[', group_concat( CASE WHEN `fileid` = 0 THEN `link`  ELSE `fileid` END), ']')  AS fileIds,
            min(`lessonId`) AS lessonId
          FROM course_material WHERE source ='coursematerial' AND lessonId >0 
          AND  lessonId NOT IN (SELECT DISTINCT   (CASE WHEN `migrateLessonId` IS NULL THEN 0 ELSE `migrateLessonId` END) AS lessonId FROM `download_activity`) GROUP BY lessonId
          "
        );
    }
}
