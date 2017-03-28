<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir').'../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System:SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System:SettingService')->set('crontab_next_executed_time', time());
    }

    protected function getStep($index)
    {
        $oldSteps = array(
            'c2ActivityLearnLog', // ?
        );

        $steps = array(
            'CourseSetMigrate',
            'CourseMigrate',

            'Lesson2CourseTaskMigrate',
            'Lesson2CourseChapterMigrate',
            'Lesson2ActivityMigrate',

            'UpdateActivity',
            'UpdateCourseTask',

            'CourseTaskRelaCourseChapter',
            'ActivityRelaCourseTask',

            'Lesson2VideoActivityMigrate',
            'ActivityRelaVideoActivity',

            'Lesson2TextActivityMigrate',
            'ActivityRelaTextActivity',

            'Lesson2AudioActivityMigrate',
            'ActivityRelaAudioActivity',

            'Lesson2FlashActivityMigrate',
            'ActivityRelaFlashActivity',

            'Lesson2PptActivityMigrate',
            'ActivityRelaPptActivity',

            'Lesson2DocActivityMigrate',
            'ActivityRelaDocActivity',

            'Lesson2LiveActivityMigrate',
            'ActivityRelaLiveActivity',

            'CourseLessonView2CourseTaskView',
            'CourseLessonLearn2CourseTaskResult',

            'CourseMaterial2DownloadActivityMigrate',
            'UpdateCourseChapter',

            'TestpaperMigrate',
            'TestpaperItemMigrate',
            'HomeworkMigrate',
            'ExerciseMigrate',

            'Exercise2CourseTaskMigrate',
            'Homework2CourseTasMigrate',

            'TestpaperResultMigrate',
            'TestpaperItemResultMigrate',

            'HomeworkResultMigrate',
            'HomeworkItemResultMigrate',

            'ExerciseResultMigrate',
            'ExerciseItemMigrate',

            'QuestionMigrate',
            'QuestionFavoriteMigrate',

            'TagOwnerMigrate',

            'AfterAllCourseTaskMigrate',
            'OtherMigrate',

            'LogMigrate',
        );

        if ($index > count($steps) - 1) {
            return '';
        }

        return $steps[$index];
    }

    protected function getIndexAndPage($index)
    {
        if ($index == 0) {
            return array(0, 1);
        }

        return explode('-', $index);
    }

    protected function setIndexAndPage($index, $page)
    {
        return "{$index}-{$page}";
    }

    protected function batchUpdate($index)
    {
        $indexAndPage = $this->getIndexAndPage($index);
        $index = $indexAndPage[0];
        $page = 1;
        if (!empty($indexAndPage[1])) {
            $page = $indexAndPage[1];
        }

        $method = $this->getStep($index);
        if (empty($method)) {
            return;
        }

        require_once '8.0.0/AbstractMigrate.php';
        $file = "8.0.0/{$method}.php";
        require_once $file;
        $migrate = new $method($this->kernel);

        $this->logger('info', "开始迁移 {$method}");
        $nextPage = $migrate->update($page);
        $this->logger('info', "迁移 {$method} 成功");

        if (!empty($nextPage)) {
            return array(
                'index' => $this->setIndexAndPage($index, $nextPage),
                'message' => '正在升级数据...',
                'progress' => 0,
            );
        }

        return array(
            'index' => $this->setIndexAndPage($index + 1, 1),
            'message' => '正在升级数据...',
            'progress' => 0,
        );
    }

    protected function c2ActivityLearnLog()
    {
        if (!$this->isTableExist('activity_learn_log')) {
            $this->exec(
                "
                CREATE TABLE `activity_learn_log` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `event` varchar(255) NOT NULL DEFAULT '' COMMENT '',
                  `data` text COMMENT '',
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `courseTaskId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `learnedTime` int(11) DEFAULT 0,
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        // $this->exec("
        //     insert into `activity_learn_log` (
        //       id
        //       ,activityId
        //       ,userId
        //       ,event
        //       ,data
        //       ,watchTime
        //       ,courseTaskId
        //       ,learnedTime
        //       ,createdTime
        //     ) values select
        //       id
        //       ,lessonId
        //       ,userId
        //       ,learnedTime
        //     from course_lesson_learn where id not in (select id from activity_learn_log)
        // ");
    }

    protected function c2testpaperMigrate()
    {
        $this->getConnection()->exec(
            "
            CREATE TABLE IF NOT EXISTS `c2_testpaper` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `name` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
              `description` text COMMENT '试卷说明',
              `courseId` int(10) NOT NULL DEFAULT '0',
              `lessonId` int(10) NOT NULL DEFAULT '0',
              `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：秒)',
              `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷生成/显示模式',
              `target` varchar(255) NOT NULL DEFAULT '',
              `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状态：draft,open,closed',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
              `passedCondition` text,
              `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
              `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
              `metas` text COMMENT '题型排序',
              `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id',
              `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
              `courseSetId` int(11) unsigned NOT NULL DEFAULT '0',
              `oldTestId` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `c2_testpaper_item` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目',
              `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
              `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
              `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
              `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
              `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源testpaper_item的id',
              `oldItemId` int(11) unsigned NOT NULL DEFAULT '0',
              `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `c2_testpaper_result` (
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
              `oldResultId` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `c2_testpaper_item_result` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷题目id',
              `testId` int(10) unsigned NOT NULL DEFAULT '0',
              `resultId` int(10) NOT NULL DEFAULT '0' COMMENT '试卷结果ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `questionId` int(10) unsigned NOT NULL DEFAULT '0',
              `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none',
              `score` float(10,1) NOT NULL DEFAULT '0.0',
              `answer` text,
              `teacherSay` text,
              `pId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id',
              `oldItemResultId` int(11) unsigned NOT NULL DEFAULT '0',
              `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
              PRIMARY KEY (`id`),
              KEY `testPaperResultId` (`resultId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `testpaper_activity` (
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

        $this->testpaperUpgrade();
        $this->homeworkUpgrade();
        $this->exerciseUpdate();
    }

    protected function testpaperUpgrade()
    {
        $sql = "SELECT * FROM testpaper WHERE id NOT IN (SELECT id FROM c2_testpaper WHERE type = 'testpaper')";
        $testpapers = $this->getConnection()->fetchAll($sql);
        foreach ($testpapers as $testpaper) {
            $targetArr = explode('/', $testpaper['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonId = 0;
            if (!empty($targetArr[1])) {
                $lessonArr = explode('-', $targetArr[1]);
                $lessonId = $lessonArr[1];
            }
            $passedCondition = empty($testpaper['passedStatus']) ? '' : json_encode(array($testpaper['passedStatus']));

            $courseSetId = $courseArr[1];

            $insertSql = "INSERT INTO c2_testpaper (
                id,
                name,
                description,
                courseId,
                lessonId,
                limitedTime,
                pattern,
                target,
                status,
                score,
                passedCondition,
                itemCount,
                createdUserId,
                createdTime,
                updatedUserId,
                updatedTime,
                metas,
                copyId,
                type,
                courseSetId,
                oldTestId
            ) VALUES (
                {$testpaper['id']},
                '".$testpaper['name']."',
                '".$testpaper['description']."',
                {$courseSetId},
                {$lessonId},
                {$testpaper['limitedTime']},
                'questionType',
                '".$testpaper['target']."',
                '".$testpaper['status']."',
                {$testpaper['score']},
                '".$passedCondition."',
                {$testpaper['itemCount']},
                {$testpaper['createdUserId']},
                {$testpaper['createdTime']},
                {$testpaper['updatedUserId']},
                {$testpaper['updatedTime']},
                '".$testpaper['metas']."',
                {$testpaper['copyId']},
                'testpaper',
                {$courseSetId},
                {$testpaper['id']}
                )";
            $this->getConnection()->exec($insertSql);
        }

        //testpaper_item
        $sql = "INSERT INTO c2_testpaper_item (
            id,
            testId,
            seq,
            questionId,
            questionType,
            parentId,
            score,
            missScore,
            oldItemId,
            type
        ) SELECT
            id,
            testId,
            seq,
            questionId,
            questionType,
            parentId,
            score,
            missScore,
            id,
            'testpaper' FROM testpaper_item
            WHERE id NOT IN (SELECT `id` FROM `c2_testpaper_item`)";
        $this->getConnection()->exec($sql);

        //testpaper_result
        $sql = "INSERT INTO c2_testpaper_result(
            id,
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
            oldResultId,
            type
        ) SELECT
            id,
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
            id AS oldResultId,
            'testpaper'
            FROM testpaper_result WHERE id NOT IN (SELECT id FROM c2_testpaper_result)";
        $this->getConnection()->exec($sql);

        $sql = "SELECT * FROM c2_testpaper_result WHERE id NOT IN (SELECT id FROM c2_testpaper_result WHERE type = 'testpaper') and type = 'testpaper'";
        $newTestpaperResults = $this->getConnection()->fetchAll($sql);
        foreach ($newTestpaperResults as $testpaperResult) {
            $targetArr = explode('/', $testpaperResult['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonArr = explode('-', $targetArr[1]);

            $courseSql = 'SELECT * FROM c2_course WHERE id = '.$courseArr[1];
            $course = $this->getConnection()->fetchAssoc($courseSql);

            $lessonId = empty($lessonArr[1]) ? 0 : $lessonArr[1];

            $sql = "UPDATE c2_testpaper_result SET
                courseId = {$course['id']},
                courseSetId = {$course['courseSetId']},
                lessonId = {$lessonId}
                WHERE id = {$testpaperResult['id']}";

            $this->getConnection()->exec($sql);
        }

        //testpaper_item_result
        $sql = "INSERT INTO c2_testpaper_item_result (
            id,
            itemId,
            testId,
            resultId,
            userId,
            questionId,
            status,
            score,
            answer,
            teacherSay,
            pId,
            oldItemResultId,
            type
        ) SELECT
            id,
            itemId,
            testId,
            testPaperResultId,
            userId,
            questionId,
            status,
            score,
            answer,
            teacherSay,
            pId,
            id,
            'testpaper'
            FROM testpaper_item_result WHERE id NOT IN (SELECT id FROM c2_testpaper_item_result)";
        $this->getConnection()->exec($sql);

        $sql = 'UPDATE c2_testpaper_item_result AS ir, c2_testpaper as t SET ir.testId = t.id WHERE t.oldTestId = ir.testId';
        $this->getConnection()->exec($sql);

        $sql = 'UPDATE c2_testpaper_item_result AS ir, c2_testpaper_result AS tr SET ir.resultId = tr.id WHERE tr.oldResultId = ir.resultId';
        $this->getConnection()->exec($sql);

        $this->testpaperActivity();

        $sql = "UPDATE c2_testpaper_result AS tr, course_task as ct SET tr.lessonId = ct.activityId WHERE tr.lessonId = ct.id AND tr.type='testpaper'";
        $this->exec($sql);
    }

    protected function homeworkUpgrade()
    {
        $sql = "show tables like 'homework'";
        $result = $this->getConnection()->fetchAssoc($sql);
        if (!$result) {
            return;
        }

        $sql = "SELECT * FROM homework WHERE id not IN (SELECT oldTestId FROM c2_testpaper WHERE type = 'homework')";
        $homeworks = $this->getConnection()->fetchAll($sql);
        if (!$homeworks) {
            return;
        }

        foreach ($homeworks as $homework) {
            $courseSetId = $homework['courseId'];

            $passedCondition = !empty($homework['correctPercent']) ? $homework['correctPercent'] : null;

            $insertSql = "INSERT INTO c2_testpaper (
                name,
                description,
                courseId,
                lessonId,
                limitedTime,
                pattern,
                target,
                status,
                score,
                passedCondition,
                itemCount,
                createdUserId,
                createdTime,
                updatedUserId,
                updatedTime,
                metas,
                copyId,
                type,
                courseSetId,
                oldTestId
            ) VALUES (
                '',
                '".$homework['description']."',
                {$homework['courseId']},
                {$homework['lessonId']},
                0,
                'questionType',
                '',
                'open',
                0,
                '".$passedCondition."',
                {$homework['itemCount']},
                {$homework['createdUserId']},
                {$homework['createdTime']},
                {$homework['updatedUserId']},
                {$homework['updatedTime']},
                null,
                {$homework['copyId']},
                'homework',
                {$courseSetId},
                {$homework['id']}
            )";

            $this->getConnection()->exec($insertSql);
            $homeworkId = $this->getConnection()->lastInsertId();
            $homeworkNew = $this->getConnection()->fetchAssoc("SELECT * FROM c2_testpaper WHERE id={$homeworkId}");

            if ($homework['copyId'] == 0) {
                $subSql = "UPDATE c2_testpaper SET copyId = {$homeworkNew['id']} WHERE copyId = {$homework['id']} AND type = 'homework'";
                $this->exec($subSql);
            }

            //homework_item
            $itemSql = "SELECT * FROM homework_item WHERE homeworkId = {$homework['id']} AND id NOT IN (SELECT oldItemId FROM c2_testpaper_item WHERE type = 'homework' AND testId = {$homework['id']})";
            $items = $this->getConnection()->fetchAll($itemSql);

            if (!$items) {
                continue;
            }

            foreach ($items as $item) {
                $sql = "INSERT INTO c2_testpaper_item (
                    testId,
                    seq,
                    questionId,
                    questionType,
                    parentId,
                    score,
                    missScore,
                    oldItemId
                ) VALUES (
                    {$homeworkNew['id']},
                    {$item['seq']},
                    {$item['questionId']},
                    '".$item['questionType']."',
                    {$item['parentId']},
                    {$item['score']},
                    {$item['missScore']},
                    {$item['id']}
                )";
                $this->getConnection()->exec($sql);
            }
        }

        $sql = "INSERT INTO c2_testpaper_result (
                paperName,
                testId,
                userId,
                courseId,
                lessonId,
                teacherSay,
                rightItemCount,
                passedStatus,
                updateTime,
                status,
                checkTeacherId,
                checkedTime,
                usedTime,
                type,
                courseSetId,
                oldResultId
            )SELECT
                '',
                homeworkId,
                userId,
                courseId,
                lessonId,
                teacherSay,
                rightItemCount,
                passedStatus,
                updatedTime,
                status,
                checkTeacherId,
                checkedTime,
                usedTime,
                'homework',
                courseId AS courseSetId,
                id AS oldResultId FROM homework_result WHERE id NOT IN (SELECT oldResultId FROM c2_testpaper_result WHERE type = 'homework')";
        $this->exec($sql);

        $sql = "UPDATE c2_testpaper_result AS tr,(SELECT id,oldTestId FROM c2_testpaper WHERE type ='homework') AS tmp SET testId = tmp.id WHERE tr.type = 'homework' AND tmp.oldTestId = tr.testId";
        $this->exec($sql);

        //需要与刘洋洋那边做好后，最终确认 lesson->activityId
        $sql = "UPDATE c2_testpaper_result AS tr,(SELECT id,mediaId FROM activity) AS tmp SET lessonId = tmp.Id WHERE tr.type = 'homework' AND tmp.mediaId = tr.testId";
        $this->exec($sql);

        $sql = "INSERT INTO c2_testpaper_item_result (
            testId,
            resultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            oldItemResultId,
            type
        ) SELECT
            homeworkId,
            homeworkResultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            id AS oldItemResultId,
            'homework'
            FROM homework_item_result WHERE id NOT IN (SELECT oldItemResultId FROM c2_testpaper_item_result where type = 'homework')";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS rt,(SELECT id,oldTestId FROM c2_testpaper WHERE type = 'homework') AS tmp SET rt.testId = tmp.id WHERE rt.type = 'homework' AND rt.testId = tmp.oldTestId;";
        $this->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS rt,(SELECT id,oldResultId FROM c2_testpaper_result WHERE type = 'homework') AS tmp SET rt.resultId = tmp.oldResultId WHERE rt.type = 'homework' AND rt.resultId = tmp.oldResultId;";
        $this->exec($sql);
    }

    protected function exerciseUpdate()
    {
        $sql = "show tables like 'exercise'";
        $result = $this->getConnection()->fetchAssoc($sql);
        if (!$result) {
            return;
        }

        $sql = "SELECT * FROM exercise WHERE id NOT IN (SELECT oldTestId FROM c2_testpaper WHERE type = 'exercise')";
        $exercises = $this->getConnection()->fetchAll($sql);
        if (!$exercises) {
            return;
        }

        foreach ($exercises as $exercise) {
            $courseSetId = $exercise['courseId'];

            $passedCondition = json_encode(array('type' => 'submit'));
            $metas = null;
            if (!empty($exercise['difficulty'])) {
                $metas['difficulty'] = $exercise['difficulty'];
            }

            if (!empty($exercise['source'])) {
                $metas['range'] = $exercise['source'];
            }

            $metas['questionTypes'] = json_decode($exercise['questionTypeRange']);
            $metas = json_encode($metas);

            $insertSql = "INSERT INTO c2_testpaper (
                name,
                description,
                courseId,
                lessonId,
                limitedTime,
                pattern,
                target,
                status,
                score,
                passedCondition,
                itemCount,
                createdUserId,
                createdTime,
                updatedUserId,
                updatedTime,
                metas,
                copyId,
                type,
                courseSetId,
                oldTestId
            ) VALUES (
                '',
                '',
                {$exercise['courseId']},
                {$exercise['lessonId']},
                0,
                'questionType',
                '',
                'open',
                0,
                '".$passedCondition."',
                {$exercise['itemCount']},
                {$exercise['createdUserId']},
                {$exercise['createdTime']},
                0,
                0,
                '".$metas."',
                {$exercise['copyId']},
                'exercise',
                {$courseSetId},
                {$exercise['id']}
            )";

            $this->getConnection()->exec($insertSql);
            $exerciseId = $this->getConnection()->lastInsertId();

            $exerciseNew = $this->getConnection()->fetchAssoc("SELECT * FROM c2_testpaper WHERE id={$exerciseId}");

            if ($exercise['copyId'] == 0) {
                $subSql = "UPDATE c2_testpaper SET copyId = {$exerciseNew['id']} WHERE copyId = {$exercise['id']} AND type = 'exercise'";
                $this->exec($subSql);
            }

            //exercise_item
            $itemSql = "SELECT * FROM exercise_item WHERE exerciseId = {$exercise['id']} AND id NOT IN (SELECT oldItemId FROM c2_testpaper_item WHERE type = 'exercise' AND testId = {$exercise['id']})";
            $items = $this->getConnection()->fetchAll($itemSql);

            if (!$items) {
                continue;
            }

            foreach ($items as $item) {
                $sql = "INSERT INTO c2_testpaper_item (
                    testId,
                    seq,
                    questionId,
                    questionType,
                    parentId,
                    score,
                    missScore,
                    oldItemId,
                    type
                ) values (
                    {$exerciseNew['id']},
                    {$item['seq']},
                    {$item['questionId']},
                    '',
                    {$item['parentId']},
                    {$item['score']},
                    {$item['missScore']},
                    {$item['id']},
                    'exercise'
                )";
                $this->getConnection()->exec($sql);
            }
        }

        /*$sql = "insert into c2_testpaper_item (testId,seq,questionId,parentId,score,missScore,oldItemId,type) select exerciseId,seq,questionId,parentId,score,missScore,id as oldItemId,'exercise' from exercise_item";
        $this->exec($sql);

        $sql = "update c2_testpaper_item as it set testId = (select id from c2_testpaper where oldTestId = it.testId and type = 'exercise') where type ='exercise'";
        $this->exec($sql);

        $sql = "update c2_testpaper_item as it set it.parentId = (select id from (select * from c2_testpaper_item) as tmp where tmp.oldItemId = it.parentId and tmp.type = 'exercise') where it.type ='exercise' and it.parentId > 0";
        $this->exec($sql);*/

        $sql = "INSERT INTO c2_testpaper_result (
                testId,
                userId,
                courseId,
                lessonId,
                rightItemCount,
                updateTime,
                status,
                usedTime,
                type,
                courseSetId,
                oldResultId )
            SELECT
                exerciseId,
                userId,
                courseId,
                lessonId,
                rightItemCount,
                updatedTime,
                status,
                usedTime,
                'exercise',
                0,
                id AS oldResultId
            FROM exercise_result WHERE id NOT IN (SELECT oldResultId FROM c2_testpaper_result WHERE type = 'exercise')";
        $this->exec($sql);

        //courseId,courseSetId 跟原来的值相同，只需要改testId和lessonId
        $sql = "UPDATE c2_testpaper_result AS tr, (SELECT id,oldTestId FROM c2_testpaper WHERE type = 'exercise') as tmp set testId = tmp.id where tr.type = 'exercise' AND tr.testId = tmp.id";
        $this->exec($sql);

        //需要与刘洋洋那边做好后，最终确认 lesson->activityId
        $sql = "UPDATE c2_testpaper_result AS tr, (SELECT id,mediaId FROM activity WHERE mediaType = 'exercise') as tmp set lessonId = tmp.id where tr.type = 'exercise' AND tr.testId = tmp.mediaId";
        $this->exec($sql);

        $sql = "INSERT INTO c2_testpaper_item_result (
            testId,
            resultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            oldItemResultId,
            type
        ) SELECT
            exerciseId,
            exerciseResultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            id AS oldItemResultId,
            'exercise' FROM exercise_item_result WHERE id NOT IN (SELECT oldItemResultId FROM c2_testpaper_item_result WHERE type = 'exercise')";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS rt,(SELECT id ,oldTestId FROM c2_testpaper WHERE type = 'exercise') AS tmp SET
            rt.testId = tmp.id WHERE rt.type = 'exercise' AND tmp.oldTestId = rt.testId ";
        $this->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS rt,(SELECT id,oldResultId FROM c2_testpaper_result WHERE type = 'exercise') AS tmp SET
            rt.resultId = tmp.id WHERE rt.type = 'exercise' AND tmp.oldResultId = rt.resultId ";
        $this->exec($sql);
    }

    protected function testpaperActivity()
    {
        if (!$this->isFieldExist('testpaper_activity', 'lessonId')) {
            $this->exec('ALTER TABLE `testpaper_activity` add `lessonId` int(10) ;');
        }

        $sql = "INSERT INTO testpaper_activity (
            id,
            mediaId,
            checkType,
            finishCondition,
            requireCredit,
            doTimes,
            redoInterval
        )SELECT
            cl.id,
            cl.mediaId,
            'score',
            '{\"type\":\"submit\",\"finishScore\":\"0\"}',
            cl.requireCredit,
            case when cle.doTimes is null then 0 else cle.doTimes end as doTimes,
            case when cle.redoInterval is null then 0 else cle.redoInterval end as redoInterval
            FROM course_lesson AS cl
            LEFT JOIN
            course_lesson_extend AS cle
            ON cl.id=cle.id
            WHERE cl.type='testpaper' AND cl.id NOT IN (SELECT id FROM testpaper_activity)";
        $this->getConnection()->exec($sql);

        $sql = 'UPDATE testpaper_activity AS ta,(SELECT id,limitedTime,oldTestId FROM c2_testpaper) AS tmp SET ta.mediaId = tmp.id, ta.limitedTime = tmp.limitedTime WHERE tmp.oldTestId = ta.mediaId';
        $this->getConnection()->exec($sql);

        $this->exec(
            "
          UPDATE  `activity` AS ay ,`testpaper_activity` AS ty   SET ay.`mediaId`  =  ty.id
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'testpaper';
         "
        );
    }

    protected function c2QuestionMigrate()
    {
        if (!$this->isFieldExist('question', 'courseId')) {
            $this->exec(
                "
                ALTER TABLE question add courseId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `target`
            "
            );
        }

        if (!$this->isFieldExist('question', 'courseSetId')) {
            $this->exec(
                "
                ALTER TABLE `question` ADD COLUMN `courseSetId` INT(10) NOT NULL DEFAULT '0'  AFTER `target`
            "
            );
        }

        if (!$this->isFieldExist('question', 'lessonId')) {
            $this->exec(
                "
                ALTER TABLE question add lessonId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `courseId`
            "
            );
        }

        $sql = 'SELECT * FROM question';
        $questions = $this->getConnection()->fetchAll($sql);

        foreach ($questions as $question) {
            $targetArr = explode('/', $question['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonId = 0;
            if (!empty($targetArr[1])) {
                $lessonArr = explode('-', $targetArr[1]);
                $lessonId = $lessonArr[1];
            }

            $sql = "UPDATE question set courseId = {$courseArr[1]},courseSetId = {$courseArr[1]},lessonId={$lessonId} WHERE id = {$question['id']}";
            $this->exec($sql);
        }

        if (!$this->isFieldExist('question_favorite', 'targetType')) {
            $this->exec(
                "
                ALTER TABLE question_favorite ADD targetType VARCHAR(50) NOT NULL DEFAULT '' AFTER `questionId`
            "
            );
        }

        if (!$this->isFieldExist('question_favorite', 'targetId')) {
            $this->exec(
                "
                ALTER TABLE question_favorite ADD targetId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `targetType`
            "
            );
        }

        $sql = 'SELECT * FROM question_favorite';
        $favorites = $this->getConnection()->fetchAll($sql);

        foreach ($favorites as $favorite) {
            $targetArr = explode('-', $favorite['target']);

            $sql = "UPDATE question_favorite set targetId = {$targetArr[1]},targetType='".$targetArr[0]."' WHERE id = {$favorite['id']}";
            $this->exec($sql);
        }
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param  string                         $statement
     * @throws \Doctrine\DBAL\DBALException
     * @return int                            the number of affected rows
     */
    protected function exec($statement)
    {
        return $this->getConnection()->exec($statement);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    protected function getCourseChapterDao()
    {
        return ServiceKernel::instance()->getBiz()->dao('Course:CourseChapterDao');
    }

    protected function logger($level, $message)
    {
        $data = date('Y-m-d H:i:s').' ['.$level.'] 6.17.9 '.$message.PHP_EOL;
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    protected function getLoggerFile()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/logs/upgrade.log';
    }
}

abstract class AbstractUpdater
{
    protected $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return \Topxia\Service\Common\Connection
     */
    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}
