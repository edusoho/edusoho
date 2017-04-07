<?php

class Lesson2VideoActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
    	if (!$this->isTableExist('activity_video')) {
            $this->getConnection()->exec(
                "
                CREATE TABLE `activity_video` (
                  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
                  `mediaId` int(10) NOT NULL DEFAULT 0 COMMENT '媒体文件ID',
                  `mediaUri` text COMMENT '媒体文件资UR',
                  `finishType` varchar(60) NOT NULL DEFAULT 'end' COMMENT '完成类型',
                  `finishDetail` text NOT NULL DEFAULT '0' COMMENT '完成条件',
                   PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';
            "
            );
        }
        if (!$this->isFieldExist('activity_video', 'migrateLessonId')) {
            $this->exec("alter table `activity_video` add `migrateLessonId` int(10) default 0;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE type ='video' and `id` NOT IN (SELECT migrateLessonId FROM `activity_video`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->getConnection()->exec(
            "
            insert into `activity_video` (
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
                '0',
                `id`
            from `course_lesson` where  type ='video' and `id` not in (select `migrateLessonId` from `activity_video`) order by id limit 0, {$this->perPageCount};
        "
        );

        return $page;
    }
}