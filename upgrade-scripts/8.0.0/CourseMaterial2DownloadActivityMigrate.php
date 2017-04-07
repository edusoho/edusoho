<?php

class CourseMaterial2DownloadActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->migrateTableStructure();

        //create table course_material_v8 and dumplcate date from course_material

        $this->dumplicateCourseMaterialDatas();
        $this->exec(' UPDATE `course_material_v8` SET `courseSetId` = courseId WHERE `courseSetId` is null;');
        $this->exec(" UPDATE `course_material_v8` SET  `source`= 'courseactivity' WHERE source = 'courselesson';");

        $this->proccessDownloadActivity();
    }

    protected function migrateTableStructure()
    {
        if (!$this->isTableExist('activity_download')) {
            $this->exec(
                "
              CREATE TABLE `activity_download` (
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

        if (!$this->isTableExist('download_file_record')) {
            $this->exec("
              CREATE TABLE `download_file_record` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                `downloadActivityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属活动ID',
                `materialId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件ID',
                `fileId` varchar(1024) DEFAULT '' COMMENT '文件ID',
                `link` varchar(1024) DEFAULT '' COMMENT '链接地址',
                `createdTime` int(10) unsigned NOT NULL COMMENT '下载时间',
                `userId` int(10) unsigned NOT NULL COMMENT '下载用户ID',
                PRIMARY KEY (`id`),
                KEY `createdTime` (`createdTime`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('activity_download', 'migrateLessonId')) {
            $this->exec('alter table `activity_download` add `migrateLessonId` int(10) default 0;');
        }

        if (!$this->isFieldExist('course_material', 'courseSetId')) {
            $this->exec('alter table `course_material` add `courseSetId` int(10);');
        }
    }

    protected function dumplicateCourseMaterialDatas()
    {
        if (!$this->isTableExist('course_material_v8')) {
            $this->exec("
            CREATE TABLE `course_material_v8` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程资料ID',
              `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课程ID',
              `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课时ID',
              `title` varchar(1024) NOT NULL COMMENT '资料标题',
              `description` text COMMENT '资料描述',
              `link` varchar(1024) NOT NULL DEFAULT '' COMMENT '外部链接地址',
              `fileId` int(10) unsigned NOT NULL COMMENT '资料文件ID',
              `fileUri` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件URI',
              `fileMime` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件MIME',
              `fileSize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小',
              `source` varchar(50) NOT NULL DEFAULT 'coursematerial',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料创建人ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '资料创建时间',
              `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id',
              `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
              `courseSetId` int(10) DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        $this->exec(
          '
           INSERT INTO `course_material_v8`  SELECT * FROM `course_material` WHERE  id NOT IN (SELECT id FROM  `course_material_v8`);
        ');
    }

    protected function proccessDownloadActivity()
    {
        $this->exec(
          "
          INSERT INTO `activity_download`
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
          FROM course_material_v8 WHERE source ='coursematerial' AND lessonId >0
          AND  lessonId NOT IN (SELECT DISTINCT   (CASE WHEN `migrateLessonId` IS NULL THEN 0 ELSE `migrateLessonId` END) AS lessonId FROM `activity_download`) GROUP BY lessonId
          "
        );
    }
}
