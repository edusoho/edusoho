<?php

class CourseMaterial2DownloadActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->migrateTableStructure();

        //create table course_material_v8 and dumplcate date from course_material
        
        $this->dumplicateCourseMaterialDatas();

        $this->exec(' UPDATE `course_material_v8` SET `courseSetId` = courseId WHERE `courseSetId`<>`courseId`;');
        $this->exec(" UPDATE `course_material_v8` SET  `source`= 'courseactivity' WHERE source = 'courselesson';");

        // refactor: 数据完整性校验，校验同一个lesson下是否全部的资料已经迁移完成
        // $countSql = "SELECT count(id) FROM course_material WHERE source ='coursematerial' AND type = 'course' AND  lessonId > 0 AND lessonId NOT IN (SELECT lessonId FROM `download_activity`)";
        // $count = $this->getConnection()->fetchColumn($countSql);
        // if ($count == 0) {
        //     return;
        // }

        $this->proccessDownloadActivity();
        $this->preccessActivity();
        $this->proccessCourseTask();
        $this->processRelations();
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
            $this->exec('alter table `course_material` add `courseSetId` int(10);');
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
            concat('[', group_concat( CASE WHEN `fileid` = 0 THEN `link`  ELSE `fileid` END), ']')  as fileIds,
            min(`lessonId`) as lessonId
          FROM course_material_v8 WHERE source ='coursematerial' AND lessonId >0 GROUP BY lessonId
          AND  lessonId NOT IN (SELECT `migrateLessonId` FROM `download_activity`);
          "
        );
    }

    protected function preccessActivity()
    {
        $this->exec(
            "
        INSERT INTO activity
        (
          `title`,
          `mediaType`,
          `fromCourseId`,
          `fromCourseSetId`,
          `fromUserId`,
          `createdTime`,
          `updatedTime`,
          `migrateLessonId`
        )
        SELECT
          '下载',
          'download',
          max(`courseId`) AS `courseId`,
          max(`courseSetId`) AS courseSetId,
          max(`userId`) AS userId ,
          max(`createdTime`) AS createdTime,
          max(`createdTime`) AS updatedTime,
          max(`lessonId`) AS lessonId
        FROM course_material_v8 WHERE source ='coursematerial' AND TYPE = 'course' AND  lessonid >0 GROUP BY  lessonid
          AND  lessonId NOT IN (SELECT  `migrateLessonId` FROM `activity` WHERE mediaType = 'download');
        "
        );
    }

    // refactor: id not in问题
    protected function proccessCourseTask()
    {
        $this->exec(
            "
        INSERT INTO `course_task`
        (
          `courseId`,
          `fromCourseSetId`,
          `seq`,
          `categoryId`,
          `title`,
          `status`,
          `createdUserId`,
          `createdTime`,
          `updatedTime`,
          `mode`,
          `number`,
          `type`,
          `migrateLessonId`
        )
        SELECT
          `courseId`,
          `courseId`,
          `seq`,
          `chapterId`,
          '下载' AS title,
          `status`,
          `userId`,
          `createdTime`,
          `updatedTime`,
          'extraClass',
          `number`,
          'download',
          `id`
        FROM  `course_lesson`  WHERE id IN
        (
          SELECT  max(`lessonId`) AS lessonId
          FROM course_material_v8 WHERE source ='coursematerial' AND TYPE = 'course' AND  lessonid >0 GROUP BY  lessonid
        ) AND  id NOT IN (SELECT `migrateLessonId` FROM course_task WHERE  TYPE ='download')
        "
        );
    }

    protected function processRelations()
    {
        $this->exec(
            "
         	UPDATE  `activity` AS ay ,`download_activity` AS dy SET ay.`mediaId`  =  dy.`id`
         WHERE ay.`migrateLessonId`  = dy.`migrateLessonId` AND ay.`mediaType` = 'download' AND ay.`mediaId` IS NULL;
        "
        );

        $this->exec(
        "
            UPDATE  `course_task` AS ck ,`activity` AS ay SET ck.`activityId`  =  ay.`id`
            WHERE ck.`migrateLessonId`  = ay.`migrateLessonId` AND ck.`type` = 'download' AND ck.`activityId` IS NULL;
        "
        );
    }
}
