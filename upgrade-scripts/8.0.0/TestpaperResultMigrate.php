<?php

class TestpaperResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('testpaper_result_v8')) {
            $this->getConnection()->exec("
                CREATE TABLE  `testpaper_result_v8` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
                  `paperName` varchar(255) NOT NULL DEFAULT '',
                  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'testId',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UserId',
                  `courseId` int(10) NOT NULL DEFAULT '0',
                  `lessonId` int(10) NOT NULL DEFAULT '0',
                  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
                  `objectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
                  `subjectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
                  `teacherSay` text,
                  `rightItemCount` int(10) unsigned NOT NULL DEFAULT '0',
                  `passedStatus` enum('none','excellent','good','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '考试通过状态，none表示该考试没有',
                  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷限制时间(秒)',
                  `beginTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
                  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
                  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
                  `status` enum('doing','paused','reviewing','finished') NOT NULL COMMENT '状态',
                  `target` varchar(255) NOT NULL DEFAULT '',
                  `checkTeacherId` int(10) unsigned NOT NULL DEFAULT '0',
                  `checkedTime` int(11) NOT NULL DEFAULT '0',
                  `usedTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
                  `courseSetId` int(11) unsigned NOT NULL DEFAULT '0',
                  `migrateResultId` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        $nextPage = $this->insertTestpaperResult($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateTestpaperResult();
    }

    private function updateTestpaperResult()
    {
        $sql = "SELECT * FROM testpaper_result_v8 WHERE type = 'testpaper' AND courseId = 0 AND target != '';";
        $newTestpaperResults = $this->getConnection()->fetchAll($sql);
        foreach ($newTestpaperResults as $testpaperResult) {
            $targetArr = explode('/', $testpaperResult['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonArr = explode('-', $targetArr[1]);

            $courseId = (int)$courseArr[1];
            $lessonId = empty($lessonArr[1]) ? 0 : (int)$lessonArr[1];
            $sql = "UPDATE testpaper_result_v8 SET
                courseId = {$courseId},
                courseSetId = {$courseId},
                lessonId = {$lessonId}
                WHERE id = {$testpaperResult['id']}";

            $this->getConnection()->exec($sql);
        }

        $sql = "UPDATE testpaper_result_v8 AS tr, course_task as ct SET tr.lessonId = ct.activityId WHERE tr.lessonId = ct.id AND ct.type = 'testpaper' AND tr.type='testpaper'";
        $this->getConnection()->exec($sql);
    }

    private function insertTestpaperResult($page)
    {
        $countSql = 'SELECT count(id) FROM `testpaper_result` where `id` not in (select `id` from `testpaper_result_v8`);';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $sql = "INSERT INTO testpaper_result_v8(
            paperName,
            testId,
            userId,
            courseId,
            lessonId,
            score,
            objectiveScore,
            subjectiveScore,
            teacherSay,
            rightItemCount,
            passedStatus,
            limitedTime,
            beginTime,
            endTime,
            updateTime,
            active,
            status,
            target,
            checkTeacherId,
            checkedTime,
            usedTime,
            migrateResultId,
            type
        ) SELECT
            paperName,
            testId,
            userId,
            0,
            0,
            score,
            objectiveScore,
            subjectiveScore,
            teacherSay,
            rightItemCount,
            passedStatus,
            limitedTime,
            beginTime,
            endTime,
            updateTime,
            active,
            status,
            target,
            checkTeacherId,
            checkedTime,
            usedTime,
            id AS migrateResultId,
            'testpaper'
            FROM testpaper_result WHERE id NOT IN (SELECT id FROM testpaper_result_v8) order by id limit 0, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        return $page + 1;
    }
}
