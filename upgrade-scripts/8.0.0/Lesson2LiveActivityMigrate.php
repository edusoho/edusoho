<?php 

class Lesson2LiveActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {

		if (!$this->isTableExist('live_activity')) {
            $sql = "CREATE TABLE `live_activity` (
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

		if (!$this->isFieldExist('live_activity', 'migrateLessonId')) {
            $this->exec("alter table `live_activity` add `migrateLessonId` int(10) ;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE `type`='live' and `id` NOT IN (SELECT migrateLessonId FROM `live_activity`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        $start = $this->getStart($page);
        if ($count == 0 && $count < $start) {
            return;
        }

		$sql = "INSERT INTO `live_activity` (
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
            , 0
            ,`id`
        FROM `course_lesson` where type='live' and `id` not in (select `id` from `live_activity`)
        order by id limit {$start}, {$this->perPageCount};";

        $result = $this->getConnection()->exec($sql);

        return $this->getNextPage($count, $page);
    }
}
