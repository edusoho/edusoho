<?php

class Lesson2VideoActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
    	if (!$this->isTableExist('video_activity')) {
            $this->getConnection()->exec(
                "
                CREATE TABLE `video_activity` (
                  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
                  `mediaId` int(10) NOT NULL DEFAULT 0 COMMENT '媒体文件ID',
                  `mediaUri` text COMMENT '媒体文件资UR',
                  `finishType` varchar(60) NOT NULL COMMENT '完成类型',
                  `finishDetail` text NOT NULL COMMENT '完成条件',
                   PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';
            "
            );
        }
        if (!$this->isFieldExist('video_activity', 'migrateLessonId')) {
            $this->exec("alter table `video_activity` add `migrateLessonId` int(10) ;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE type ='video' and `id` NOT IN (SELECT migrateLessonId FROM `video_activity`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        $start = $this->getStart($page);
        if ($count == 0 && $count < $start) {
            return;
        }

        $this->getConnection()->exec(
            "
            insert into `video_activity` (
                `mediaSource`,
                `mediaId`,
                `mediaUri`,
                `finishType`,
                `finishDetail`,
                `migrateLessonId`
            )
            select
                `mediaSource`,
                `mediaId`,
                `mediaUri`,
                'end',
                '1',
                `id`
            from `course_lesson` where  type ='video' and `id` not in (select `migrateLessonId` from `video_activity`) order by id limit {$start}, {$this->perPageCount};
        "
        );

        return $this->getNextPage($count, $page);
    }
}