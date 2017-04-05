<?php

class Lesson2TestpaperActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('activity_testpaper')) {
            $this->exec(
                "
                CREATE TABLE `activity_testpaper` (
                  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '关联activity表的ID',
                  `mediaId` int(10) NOT NULL DEFAULT '0' COMMENT '试卷ID',
                  `doTimes` smallint(6) NOT NULL DEFAULT '0' COMMENT '考试次数',
                  `redoInterval` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '重做时间间隔(小时)',
                  `limitedTime` int(10) NOT NULL DEFAULT '0' COMMENT '考试时间',
                  `checkType` text,
                  `finishCondition` text,
                  `requireCredit` int(10) NOT NULL DEFAULT '0' COMMENT '参加考试所需的学分',
                  `testMode` varchar(50) NOT NULL DEFAULT 'normal' COMMENT '考试模式',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('activity_testpaper', 'migrateLessonId')) {
            $this->exec("alter table `activity_testpaper` add `migrateLessonId` int(10) default 0;");
        }

        $nextPage = $this->insertTestpaperActivity($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateTestpaperActivity();
    }

    private function updateTestpaperActivity()
    {
        $sql = "UPDATE activity_testpaper AS ta, testpaper_v8 tmp
        SET ta.mediaId = tmp.id, ta.limitedTime = tmp.limitedTime WHERE tmp.migrateTestId = ta.mediaId";

        $this->getConnection()->exec($sql);

        $sql = "UPDATE  `activity` AS ay ,`activity_testpaper` AS ty SET ay.`mediaId` = ty.id WHERE ay.migrateLessonId = ty.migrateLessonId   AND ay.`mediaType` = 'testpaper'";
        $this->getConnection()->exec($sql);
    }

    private function insertTestpaperActivity($page)
    {
        $countSql = "SELECT count(id) FROM `course_lesson` where `id` not in (select `id` from `activity_testpaper`) and type = 'testpaper'";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $sql = "INSERT INTO activity_testpaper (
            id,
            mediaId,
            checkType,
            finishCondition,
            requireCredit,
            doTimes,
            redoInterval,
            migrateLessonId
        )SELECT
            cl.id,
            cl.mediaId,
            'score',
            '{\"type\":\"submit\",\"finishScore\":\"0\"}',
            cl.requireCredit,
            case when cle.doTimes is null then 0 else cle.doTimes end as doTimes,
            case when cle.redoInterval is null then 0 else cle.redoInterval end as redoInterval,
            cl.id
            FROM course_lesson AS cl
            LEFT JOIN
            course_lesson_extend AS cle
            ON cl.id=cle.id
            WHERE cl.type='testpaper' AND cl.id NOT IN (SELECT id FROM activity_testpaper) order by cl.id limit 0, {$this->perPageCount}";
        $this->getConnection()->exec($sql);

        return $page + 1;
    }
}
