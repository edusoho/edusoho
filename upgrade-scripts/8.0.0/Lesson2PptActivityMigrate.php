<?php

class Lesson2PptActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('activity_ppt')) {
            $this->exec(
                "
                CREATE TABLE `activity_ppt` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `mediaId` int(11) NOT NULL,
                  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'end, time',
                  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
                  `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
                  `createdUserId` int(11) NOT NULL,
                  `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('activity_ppt', 'migrateLessonId')) {
            $this->exec('alter table `activity_ppt` add `migrateLessonId` int(10) ;');
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE `type`='ppt' and `id` NOT IN (SELECT migrateLessonId FROM `activity_ppt`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->exec(
            "
          insert into `activity_ppt`
            (
            `mediaId`,
            `finishType`,
            `finishDetail`,
            `createdTime`,
            `createdUserId`,
            `updatedTime`,
            `migrateLessonId`
            )
          select
            `mediaId`,
            'end',
            '1',
            `createdTime`,
            `userId` ,
            `updatedTime`,
            `id`
          from `course_lesson` where type ='ppt' and id not in (select `migrateLessonId` from `activity_ppt`) order by id limit 0, {$this->perPageCount};
        "
        );

        return $page+1;
    }
}
