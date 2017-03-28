<?php 

class Lesson2TextActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
    	if (!$this->isTableExist("text_activity")) {
            $this->getConnection()->exec(
                "
            CREATE TABLE `text_activity` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, time',
              `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
              `createdTime` int(10) NOT NULL,
              `createdUserId` int(11) NOT NULL,
              `updatedTime` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('text_activity', 'migrateLessonId')) {
            $this->exec("alter table `text_activity` add `migrateLessonId` int(10) ;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE `type`='text' and `id` NOT IN (SELECT migrateLessonId FROM `text_activity`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        $start = $this->getStart($page);
        if ($count == 0 && $count < $start) {
            return;
        }

        $this->getConnection()->exec(
            "
            INSERT INTO `text_activity` (
                `finishType`,
                `finishDetail`,
                `createdTime`,
                `createdUserId`,
                `updatedTime`,
                `migrateLessonId`
            )
            SELECT
                'time',
                '1',
                `createdTime`,
                `userId`,
                `updatedTime`,
                `id`
            FROM `course_lesson` WHERE  `type`='text' AND  `id` NOT IN (SELECT `migrateLessonId` FROM `text_activity`) order by id limit {$start}, {$this->perPageCount};
        "
        );

        return $this->getNextPage($count, $page);
    }
}
