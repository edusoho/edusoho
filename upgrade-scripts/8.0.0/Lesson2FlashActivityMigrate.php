<?php 

class Lesson2FlashActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
		if (!$this->isTableExist("flash_activity")) {
            $this->exec(
                "
                CREATE TABLE `flash_activity` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `mediaId` int(11) NOT NULL,
                  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, time',
                  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
                  `createdTime` int(10) NOT NULL,
                  `createdUserId` int(11) NOT NULL,
                  `updatedTime` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('flash_activity', 'migrateLessonId')) {
            $this->exec("alter table `flash_activity` add `migrateLessonId` int(10) ;");
        }

        $countSql = "SELECT count(*) from `course_lesson` WHERE `type`='flash' and `id` NOT IN (SELECT migrateLessonId FROM `flash_activity`)";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->exec(
            "
            INSERT INTO `flash_activity`
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
            FROM `course_lesson` WHERE TYPE ='flash' AND id NOT IN (SELECT `migrateLessonId` FROM `flash_activity`)
            order by id limit 0, {$this->perPageCount}
        "
        );

        return $page++;
	}
}