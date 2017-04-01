<?php

class Lesson2LiveActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('activity_live')) {
            $sql = "CREATE TABLE `activity_live` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `liveId` int(11) NOT NULL COMMENT '直播间ID',
              `liveProvider` int(11) NOT NULL COMMENT '直播供应商',
              `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态',
              `mediaId` INT(11) UNSIGNED DEFAULT 0 COMMENT '视频文件ID',
              `roomCreated` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '直播教室是否已创建',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $this->getConnection()->exec($sql);
        }

        if (!$this->isFieldExist('activity_live', 'migrateLessonId')) {
            $this->exec("alter table `activity_live` add `migrateLessonId` int(10) ;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE `type`='live' and `id` NOT IN (SELECT migrateLessonId FROM `activity_live`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        //老数据当直播回放是视频文件的时候，会修改mediaId成fileId，正常mediaId是liveId
        $sql = "INSERT INTO `activity_live` (
          `id`
          ,`liveId`
          ,`liveProvider`
          ,`replayStatus`
          ,`mediaId`
          ,`migrateLessonId`
        ) SELECT
            `id`
            ,`mediaId`
            ,`liveProvider`
            ,`replayStatus`
            , case when replayStatus = 'videoGenerated' then mediaId else 0 end
            ,`id`
        FROM `course_lesson` where type='live' and `id` not in (select `id` from `activity_live`)
        order by id limit 0, {$this->perPageCount};";

        $result = $this->getConnection()->exec($sql);

        return $page + 1;
    }
}
