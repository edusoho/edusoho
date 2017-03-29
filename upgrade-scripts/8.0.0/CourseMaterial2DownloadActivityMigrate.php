<?php

class CourseMaterial2DownloadActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->migrateTableStructure();

        //create table course_material_v8 and dumplcate date from course_material
        
        $this->dumplicateCourseMaterialDatas();

        $this->exec(' UPDATE `course_material_v8` SET `courseSetId` = courseId;');

        $this->exec(" UPDATE `course_material_v8` SET  `source`= 'courseactivity' WHERE source= 'courselesson';");

        $countSql = "SELECT count(id) FROM course_material WHERE source ='coursematerial' AND TYPE = 'course' AND  lessonid >0   AND lessonid NOT IN (SELECT lessonid FROM `download_activity`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->proccessDownloadActivity();

        $this->proccessDownloadActivity();

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
            concat('[', group_concat( CASE WHEN `fileid` = 0 THEN `link`  ELSE `fileid` END), ']')  as fileIds,
            min(`lessonId`) as lessonId
          FROM course_material WHERE source ='coursematerial' AND lessonId >0 GROUP BY lessonId
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
        FROM course_material WHERE source ='coursematerial' AND TYPE = 'course' AND  lessonid >0 GROUP BY  lessonid
          AND  lessonId NOT IN (SELECT  `migrateLessonId` FROM `activity` WHERE mediaType = 'download');
        "
        );
    }

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
          FROM course_material WHERE source ='coursematerial' AND TYPE = 'course' AND  lessonid >0 GROUP BY  lessonid
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

    //TODO remove this function
    protected function c2CourseMaterial()
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

        if (!$this->isFieldExist('download_activity', 'lessonId')) {
            $this->exec('alter table `download_activity` add `lessonId` int(10) ;');
        }

        if (!$this->isFieldExist('course_material', 'courseSetId')) {
            $this->exec('alter table `course_material` add `courseSetId` int(10) ;');
        }
        $this->exec(' UPDATE `course_material` SET `courseSetId` = courseId;');
        $this->exec(" UPDATE `course_material` SET  `source`= 'courseactivity' WHERE source= 'courselesson';");

        //查找有复习资料的记录
        $downloadMaterials = $this->getConnection()->fetchAll(
            "SELECT *  FROM course_material WHERE source ='coursematerial' AND lessonid >0"
        );

        $downloadMaterials = \AppBundle\Common\ArrayToolkit::group($downloadMaterials, 'lessonId');

        //获取已经处理过的下载资料
        $downloadActivities = $this->getConnection()->fetchAll('select * from download_activity');
        $downloadActivities = \AppBundle\Common\ArrayToolkit::column($downloadActivities, 'lessonId');

        foreach ($downloadMaterials as $lessonId => $materials) {
            if (in_array($lessonId, $downloadActivities)) {
                continue;
            }

            //合并外链和本地资料
            array_filter(
                $materials,
                function (&$material) {
                    if (empty($material['fileId'])) {
                        $material['fileId'] = $material['link'];
                    }
                }
            );

            $fileCount = count($materials);
            $fileIds = \AppBundle\Common\ArrayToolkit::column($materials, 'fileId');
            $material = array_pop($materials);

            //download_activity
            $download = array(
                'mediaCount' => $fileCount,
                'createdTime' => $material['createdTime'],
                'updatedTime' => $material['createdTime'],
                'fileIds' => json_encode($fileIds),
                'lessonId' => $lessonId,
            );

            $this->getConnection()->insert('download_activity', $download);
            $downloadId = $this->getConnection()->lastInsertId();
            //activity
            $activity = array(
                'title' => '下载',
                'mediaId' => $downloadId,
                'mediaType' => 'download',
                'fromCourseId' => $material['courseId'],
                'fromCourseSetId' => $material['courseSetId'],
                'fromUserId' => $material['userId'],
                'createdTime' => $material['createdTime'],
                'updatedTime' => $material['createdTime'],
            );

            $this->getConnection()->insert('activity', $activity);
            $activityId = $this->getConnection()->lastInsertId();

            $lesson = $this->getConnection()->fetchAssoc("SELECT * FROM `course_lesson` WHERE id = {$lessonId}  ");
            //course_task
            $task = array(
                'courseId' => $lesson['courseId'],
                'seq' => $lesson['seq'],
                'categoryId' => $lesson['chapterId'],
                'activityId' => $activityId,
                'title' => '下载',
                'status' => $lesson['status'],
                'createdUserId' => $lesson['userId'],
                'createdTime' => $lesson['createdTime'],
                'updatedTime' => $lesson['updatedTime'],
                'mode' => 'extraClass',
                'number' => $lesson['number'],
                'type' => 'download',
                'lessonId' => $lessonId,
                'fromCourseSetId' => $lesson['courseId'],
            );

            $this->getConnection()->insert('course_task', $task);
        }
    }
}
