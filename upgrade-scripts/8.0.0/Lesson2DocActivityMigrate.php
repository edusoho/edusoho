<?php 

class Lesson2DocActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
    	if (!$this->isTableExist('doc_activity')) {
            $this->exec(
                "
                CREATE TABLE `doc_activity` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `mediaId` int(11) NOT NULL,
                  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, detail',
                  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
                  `createdTime` int(10) NOT NULL,
                  `createdUserId` int(11) NOT NULL,
                  `updatedTime` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('doc_activity', 'migrateLessonId')) {
            $this->exec("alter table `doc_activity` add `migrateLessonId` int(10) ;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE `type`='document' and `id` NOT IN (SELECT migrateLessonId FROM `doc_activity`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->exec(
            "
            INSERT INTO `doc_activity`
            (
            `mediaId`,
            `finishType`,
            `finishDetail`,
            `createdTime`,
            `createdUserId`,
            `updatedTime`,
            `migrateLessonId`
            )
            SELECT
                `mediaId`,
                'time',
                '1',
                `createdTime`,
                `userId` ,
                `updatedTime`,
                `id`
            FROM `course_lesson` WHERE TYPE ='document' AND id NOT IN (SELECT `migrateLessonId` FROM `doc_activity`)
            order by id limit 0, {$this->perPageCount};
        "
        );

        return $page+1;
    }
}