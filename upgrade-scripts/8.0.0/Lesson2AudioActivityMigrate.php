<?php 

class Lesson2AudioActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('audio_activity')) {
            $this->exec(
                "
                CREATE TABLE `audio_activity` (
                  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `mediaId` int(10) DEFAULT NULL COMMENT '媒体文件ID',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='音频活动扩展表';
            "
            );
        }

        if (!$this->isFieldExist('audio_activity', 'migrateLessonId')) {
            $this->exec("alter table `audio_activity` add `migrateLessonId` int(10) ;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE type ='audio' and `id` NOT IN (SELECT migrateLessonId FROM `audio_activity`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->exec(
            "
            insert into `audio_activity`
            (
                `mediaId`,
                `migrateLessonId`
            )
            select
              `mediaId`,
              `id`
            from `course_lesson` where  type ='audio' and   `id` not in (select `migrateLessonId` from `audio_activity`) order by id limit 0, {$this->perPageCount};
        "
        );

        return $page+1;
    }
}
