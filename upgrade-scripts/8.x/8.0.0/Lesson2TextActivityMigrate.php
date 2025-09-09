<?php 

class Lesson2TextActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
    	if (!$this->isTableExist("activity_text")) {
            $this->getConnection()->exec(
                "
            CREATE TABLE `activity_text` (
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

        if (!$this->isFieldExist('activity_text', 'migrateLessonId')) {
            $this->exec("alter table `activity_text` add `migrateLessonId` int(10) default 0;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE `type`='text' and `id` NOT IN (SELECT migrateLessonId FROM `activity_text`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->getConnection()->exec(
            "
            INSERT INTO `activity_text` (
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
            FROM `course_lesson` WHERE  `type`='text' AND  `id` NOT IN (SELECT `migrateLessonId` FROM `activity_text`) order by id limit 0, {$this->perPageCount};
        "
        );

        return $page+1;
    }
}
