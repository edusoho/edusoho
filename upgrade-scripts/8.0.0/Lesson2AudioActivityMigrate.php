<?php 

class Lesson2AudioActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('activity_audio')) {
            $this->exec(
                "
                CREATE TABLE `activity_audio` (
                  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `mediaId` int(10) DEFAULT NULL COMMENT '媒体文件ID',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='音频活动扩展表';
            "
            );
        }

        if (!$this->isFieldExist('activity_audio', 'migrateLessonId')) {
            $this->exec("alter table `activity_audio` add `migrateLessonId` int(10) ;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE type ='audio' and `id` NOT IN (SELECT migrateLessonId FROM `activity_audio`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->exec(
            "
            insert into `activity_audio`
            (
                `mediaId`,
                `migrateLessonId`
            )
            select
              `mediaId`,
              `id`
            from `course_lesson` where  type ='audio' and   `id` not in (select `migrateLessonId` from `activity_audio`) order by id limit 0, {$this->perPageCount};
        "
        );

        return $page+1;
    }
}
