<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Topxia\Service\Common\ServiceKernel;

class EduSohoUpgrade extends AbstractUpdater
{
    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            } else {
                $this->logger('info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger('error', $e->getTraceAsString());
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'alterTables', //更新数据库字段
            'freshClassroomMemberLastLearnTimeData', //更新classroom_member.lastLearnTime字段为NULL的内容
            'freshCourseMemberLastLearnTimeData', //更新course_member.lastLearnTime字段为NULL的内容
            'freshCourseMemberIsLearned0Data', //更新course_member学习的状态，isLearned = 0
            'freshCourseMemberIsLearned1Data', //更新course_member学习的状态，isLearned = 1
            'registerJob', //注册签到的统计JOB
            'refreshClassroomIncome', //更新班级的收入数据
            'refreshClassroomTaskNums', //更新班级的任务数据
            'refreshSignUserStatistics', //更新签到统计数据
            'refreshCourseMemberStartLearnTime', //更新course_member.startLearnTime
            'refreshClassroomMemberNoteNum',    //更新classroom_member.noteNum
            'refreshClassroomMemberQuestionNum',    //更新classroom_member.questionNum
            'refreshClassroomMemberThreadNum',  //更新classroom_member.threadNum
            'refreshClassroomMemberLearnedTaskNums',    //更新classroom_member: learnedCompulsoryTaskNum, learnedElectiveTaskNum
            'refreshClassroomMemberFinishedData', //更新classroom_member的finished信息
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if (1 == $page) {
            ++$step;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }
    }

    public function registerJob()
    {
        if (!empty($this->getSchedulerService()->getJobByName('RefreshUserSignKeepDaysJob'))) {
            $this->logger('info', "刷新连续签到天数的定时任务已存在，直接跳过");
            return 1;
        }

        $this->getSchedulerService()->register(array(
            'name' => 'RefreshUserSignKeepDaysJob',
            'expression' => '15 0 * * *',
            'class' => 'Biz\Sign\Job\RefreshUserSignKeepDaysJob',
            'args' => [],
            'misfire_threshold' => 300,
            'misfire_policy' => 'executing',
        ));

        $this->logger('info', "创建刷新连续签到天数的定时任务");

        return 1;
    }

    public function refreshClassroomIncome()
    {
        // g: goods_specs
        // c: 联表查询biz_order、biz_order_item出来的临时表: targetId, income
        // m: 根据条件筛选查询biz_order_item出来的临时表: target_id, orderId
        $updateFields = $this->getConnection()->fetchAll("
            SELECT g.targetId AS id, IF(c.income, TRUNCATE(c.income/100, 2), 0) AS income 
            FROM goods_specs g INNER JOIN (
                SELECT m.target_id AS targetId, sum(o.pay_amount) AS income 
                FROM biz_order o INNER JOIN (
                    SELECT target_id, order_id FROM biz_order_item WHERE target_type = 'classroom' AND status IN ('success', 'finished', 'paid')
                ) AS m ON m.order_id = o.id GROUP BY targetId
            ) AS c ON c.targetId = g.id AND g.goodsId IN (SELECT id FROM goods WHERE type = 'classroom');
        ");

        if (empty($updateFields)) {
            $this->logger('info', "没有班级收入需要刷新，直接跳过");
            return 1;
        }

        $this->getClassroomDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
        $total = count($updateFields);
        $this->logger('info', "批量刷新{$total}个班级收入");

        return 1;
    }

    public function refreshClassroomTaskNums($page)
    {
        // c: course_v8
        // cc: classroom_courses
        $updateFields = $this->getConnection()->fetchAll("
            SELECT cc.classroomId AS id, SUM(c.compulsoryTaskNum) AS compulsoryTaskNum, SUM(electiveTaskNum) AS electiveTaskNum 
            FROM course_v8 c INNER JOIN (
                SELECT classroomId, courseId FROM classroom_courses
            ) AS cc ON cc.courseId = c.id GROUP BY cc.classroomId;
        ");

        if (empty($updateFields)) {
            $this->logger('info', "没有班级必修课时数或选修课时数需要刷新，直接跳过");
            return 1;
        }

        $this->getClassroomDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
        $total = count($updateFields);
        $this->logger('info', "批量刷新{$total}个班级必修课时数与选修课时数");

        return 1;
    }

    public function refreshClassroomMemberNoteNum($page)
    {

        $count = $this->getConnection()->fetchColumn("SELECT count(*) FROM classroom_member;");
        $start = ($page -1) * 5000;
        if ($start >= $count) {
            return 1;
        }
        $classroomMemberIds = array_column($this->getConnection()->fetchAll("SELECT id FROM classroom_member ORDER BY ID ASC limit {$start}, 5000 ;"), 'id');
        if (empty($classroomMemberIds)) {
            return $page + 1;
        }
        $marks = str_repeat('?,', count($classroomMemberIds) - 1).'?';
        $updateFields = $this->getConnection()->fetchAll("select max(clm.id) as id, sum(cm.noteNum) AS noteNum  
            from classroom_member clm 
            inner join course_member cm 
            on clm.classroomId = cm.classroomId AND clm.userId = cm.userId 
            where clm.id in ({$marks}) group by clm.classroomId,clm.userId ORDER BY clm.classroomId,clm.userId;", $classroomMemberIds);

        if (!empty($updateFields)) {
            $this->getClassroomMemberDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
        }

        $idsString = empty($updateFields) ? '' :json_encode(array_column($updateFields, 'id'));
        $this->logger('info', "更新classroom_member的noteNum, 分页：{$page}，此次更新的id有： {$idsString}");

        return $page + 1;
    }

    public function refreshClassroomMemberQuestionNum($page)
    {
        // cm: classroom_member
        // m: group联表查询classroom_courses、course_thread出来的临时表: classroomId, userId, questionNum
        $updateFields = $this->getConnection()->fetchAll("
            SELECT cm.id AS id, cm.userId AS userId, m.questionNum AS questionNum FROM classroom_member cm INNER JOIN (
                SELECT cc.classroomId AS classroomId, ct.userId AS userId, count(*) AS questionNum 
                FROM course_thread ct 
                INNER JOIN classroom_courses cc 
                ON cc.courseId = ct.courseId AND ct.type = 'question' 
                GROUP BY cc.classroomId, ct.userId
            ) AS m ON cm.classroomId = m.classroomId AND cm.userId = m.userId;
        ");

        if (!empty($updateFields)) {
            $this->getClassroomMemberDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
            $marks = implode(',', array_column($updateFields, 'userId'));
            // cm: classroom_member
            // m: 筛选查询thread出来的临时表: classroomId, userId, questionNum
            $this->getConnection()->exec("
                UPDATE classroom_member cm INNER JOIN (
                    SELECT targetId AS classroomId, userId, count(*) AS questionNum 
                    FROM thread 
                    WHERE targetType = 'classroom'  AND type = 'question' GROUP BY targetId, userId
                ) AS m ON cm.classroomId = m.classroomId AND cm.userId = m.userId
                SET cm.questionNum = CASE
                    WHEN cm.userId IN ({$marks}) THEN cm.questionNum + m.questionNum
                    ELSE m.questionNum
                END;
            ");
        } else {
            // cm: classroom_member
            // m: 筛选查询thread出来的临时表: classroomId, userId, questionNum
            $this->getConnection()->exec("
                UPDATE classroom_member cm INNER JOIN (
                    SELECT targetId AS classroomId, userId, count(*) AS questionNum 
                    FROM thread 
                    WHERE targetType = 'classroom'  AND type = 'question' GROUP BY targetId, userId
                ) AS m ON cm.classroomId = m.classroomId AND cm.userId = m.userId 
                SET cm.questionNum = m.questionNum;
            ");
        }

        $this->logger('info', "批量刷新所有班级成员QuestionNum");
        return 1;
    }

    public function refreshClassroomMemberThreadNum($page)
    {
        // cm: classroom_member
        // m: group联表查询classroom_courses、course_thread出来的临时表: classroomId, userId, threadNum
        $updateFields = $this->getConnection()->fetchAll("
            SELECT cm.id AS id, cm.userId AS userId, m.threadNum AS threadNum FROM classroom_member cm INNER JOIN (
                SELECT cc.classroomId AS classroomId, ct.userId AS userId, count(*) AS threadNum FROM course_thread ct INNER JOIN classroom_courses cc 
                ON cc.courseId = ct.courseId AND ct.type = 'discussion' 
                GROUP BY cc.classroomId, ct.userId
            ) AS m ON cm.classroomId = m.classroomId AND cm.userId = m.userId;
        ");

        if (!empty($updateFields)) {
            $this->getClassroomMemberDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
            $marks = implode(',', array_column($updateFields, 'userId'));
            // cm: classroom_member
            // m: 筛选查询thread出来的临时表: classroomId, userId, threadNum
            $this->getConnection()->exec("
                UPDATE classroom_member cm INNER JOIN (
                    SELECT targetId AS classroomId, userId, count(*) AS threadNum 
                    FROM thread 
                    WHERE targetType = 'classroom'  AND type = 'discussion' GROUP BY targetId, userId
                ) AS m ON cm.classroomId = m.classroomId AND cm.userId = m.userId
                SET cm.threadNum = CASE
                    WHEN cm.userId IN ({$marks}) THEN cm.threadNum + m.threadNum
                    ELSE m.threadNum
                END;
            ");
        } else {
            // cm: classroom_member
            // m: 筛选查询thread出来的临时表: classroomId, userId, threadNum
            $this->getConnection()->exec("
                UPDATE classroom_member cm INNER JOIN (
                    SELECT targetId AS classroomId, userId, count(*) AS threadNum 
                    FROM thread 
                    WHERE targetType = 'classroom'  AND type = 'discussion' GROUP BY targetId, userId
                ) AS m ON cm.classroomId = m.classroomId AND cm.userId = m.userId 
                SET cm.threadNum = m.threadNum;
            ");
        }

        $this->logger('info', "批量刷新所有班级成员threadNum");

        return 1;
    }

    public function refreshSignUserStatistics($page)
    {
        $perPageCount = 1000;
        $signUserLogsCount = $this->getConnection()->fetchAll("SELECT count(*) FROM sign_user_log GROUP BY userId, targetType, targetId;");
        $count = count($signUserLogsCount);
        $start = ($page -1) *  1000;
        if ($start >= $count) {
            return 1;
        }
        // sus: sign_user_statistics
        // sul: group筛选查询sign_user_log出来的临时表: targetType, targetId, userId, lastSignTime
        $updateFields = $this->getConnection()->fetchAll("
            SELECT sus.id AS id, IF(sul.signDays, sul.signDays, 0) AS signDays, IF(sul.lastSignTime, sul.lastSignTime, 0) AS lastSignTime 
            FROM sign_user_statistics sus INNER JOIN (
                SELECT userId, targetType, targetId, COUNT(*) AS signDays, MAX(createdTime) AS lastSignTime 
                FROM sign_user_log GROUP BY userId, targetType, targetId ORDER BY userId, targetType, targetId LIMIT {$start},{$perPageCount}
            ) AS sul ON sul.userId = sus.userId AND sul.targetType = sus.targetType AND sul.targetId = sus.targetId;
        ");

        if (empty($updateFields)) {
            $this->logger('info', "没有用户的最后签到时间数据需要刷新，直接跳过");
            return $page + 1;
        }

        $this->getSignUserStatisticsDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
        return $page + 1;
    }

    public function refreshClassroomMemberLearnedTaskNums($page)
    {
        $total = $this->getConnection()->fetchColumn("SELECT COUNT(*) FROM `classroom_member`;");
        $limit = 1000;
        $start = ($page - 1) * $limit;
        if ($start >= $total) {
            return 1;
        }

        $classroomMembers = $this->getConnection()->fetchAll("SELECT id, classroomId, userId FROM `classroom_member` ORDER BY id ASC LIMIT {$start}, {$limit}");
        $marks = str_repeat('?,', count($classroomMembers) - 1).'?';
        $userIds = array_column($classroomMembers, 'userId');
        $classroomIds = array_column($classroomMembers, 'classroomId');
        $ids = array_column($classroomMembers, 'id');
        $updateFields = $this->getConnection()->fetchAll("
            SELECT clm.id AS id, clm.classroomId AS classroomId, clm.userId AS userId, 
            cm.learnedCompulsoryTaskNum AS learnedCompulsoryTaskNum, cm.learnedElectiveTaskNum AS learnedElectiveTaskNum
            FROM classroom_member clm 
            INNER JOIN (
                SELECT classroomId, userId, 
                SUM(learnedCompulsoryTaskNum) AS learnedCompulsoryTaskNum, 
                SUM(learnedElectiveTaskNum) AS learnedElectiveTaskNum 
                FROM course_member 
                WHERE classroomId IN ({$marks}) AND userId IN ({$marks}) 
                GROUP BY classroomId, userId
            ) cm ON clm.classroomId = cm.classroomId AND clm.userId = cm.userId AND clm.id IN ({$marks});
        ", array_merge($classroomIds, $userIds, $ids));


        if (!empty($updateFields)) {
            $this->getClassroomMemberDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
        }

        $idsString = empty($updateFields) ? '' :json_encode(array_column($updateFields, 'id'));
        $this->logger('info', "更新classroom_member的learnedCompulsoryTaskNum、learnedCompulsoryTaskNum, 分页：{$page}，此次更新的id有： {$idsString}");

        return $page + 1;
    }

    public function refreshCourseMemberStartLearnTime($page)
    {
        if (!empty($this->getCacheService()->get('course_member_start_learn_num_update'))) {
            return 1;
        }
        $perPageCount = 10000;
        $totalCount = $this->getConnection()->fetchColumn("SELECT count(id) FROM `course_member` WHERE startLearnTime = 0;");
        $start = ($page - 1) * $perPageCount;
        if ($start >= $totalCount) {
            $this->getCacheService()->set('course_member_start_learn_num_update', 1);
            return 1;
        }
        $ids = array_column($this->getConnection()->fetchAll("SELECT id FROM `course_member` WHERE startLearnTime = 0 ORDER BY id ASC LIMIT {$start},{$perPageCount};"), 'id');
        if (empty($ids)) {
            return $page + 1;
        }
        $marks = str_repeat('?,', count($ids) - 1).'?';
        /**
         * cmo: course_member origin
         * cmn: course_member new
         */
        $sql = "SELECT cmo.id, cmn.startLearnTime AS startLearnTime FROM course_member cmo INNER JOIN 
                    (SELECT 
                        (CASE min(ctr.createdTime) IS NULL
                        WHEN TRUE 
                        THEN 0
                        ELSE min(ctr.createdTime)
                        END) AS startLearnTime, cm.userId AS userId, cm.courseId AS courseId
                    FROM `course_member` cm 
                    LEFT JOIN course_task_result ctr 
                    ON ctr.courseId = cm.courseId AND ctr.userId = cm.userId 
                    WHERE cm.id IN ({$marks}) 
                    GROUP BY cm.courseId, cm.userId) cmn 
                ON cmo.courseId = cmn.courseId AND cmo.userId = cmn.userId;";
        $updateFields = $this->getConnection()->fetchAll($sql, $ids);
        if (empty($updateFields)) {
            return $page + 1;
        }
        $this->getCourseMemberDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
        $idsString = json_encode(array_column($updateFields, 'id'));
        $this->logger('info', "更新course_member的startLearnTime, 分页：{$page}，此次更新的id有： {$idsString}");
        return $page + 1;
    }

    public function alterTables($page)
    {
        $sqls = [
            [
                'table' => 'classroom',
                'column' => 'compulsoryTaskNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom` ADD COLUMN `compulsoryTaskNum` int(10) DEFAULT '0' COMMENT '班级下所有课程的必修任务数' AFTER `lessonNum`;",
            ],
            [
                'table' => 'classroom',
                'column' => 'electiveTaskNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom` ADD COLUMN `electiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级下所有课程的选修任务数' AFTER `compulsoryTaskNum`;",
            ],
            [
                "table" => "classroom_member",
                "column" => "isFinished",
                "action" => "add_column",
                "sql" => "ALTER TABLE `classroom_member` ADD COLUMN `isFinished` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已学完' AFTER `learnedNum`;",
            ],
            [
                'table' => 'classroom_member',
                'column' => 'finishedTime',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom_member` ADD COLUMN `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成课程时间' AFTER `isFinished`;",
            ],
            [
                'table' => 'classroom_member',
                'column' => 'learnedCompulsoryTaskNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom_member` ADD COLUMN `learnedCompulsoryTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习的必修课任务数量' AFTER `learnedNum`;",
            ],
            [
                'table' => "classroom_member",
                'column' => 'learnedElectiveTaskNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom_member` ADD COLUMN `learnedElectiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习的选修课任务数量' AFTER `learnedCompulsoryTaskNum`;",
            ],
            [
                'table' => 'classroom_member',
                'column' => 'questionNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom_member` ADD COLUMN `questionNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提问数' AFTER `threadNum`;",
            ],
            [
                'table' => 'sign_user_statistics',
                'column' => 'signDays',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `sign_user_statistics` ADD COLUMN `signDays` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到总天数' AFTER `targetId`;",
            ],
            [
                'table' => 'sign_user_statistics',
                'column' => 'lastSignTime',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `sign_user_statistics` ADD COLUMN `lastSignTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到总天数' AFTER `signDays`;",
            ],
            [
                'table' => 'course_member',
                'column' => 'startLearnTime',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `course_member` ADD COLUMN `startLearnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始学习时间' AFTER `isLearned`;",
            ],
            [
                'table' => 'classroom_member',
                'column' => 'lastLearnTime',
                'action' => 'modify',
                'sql' => "ALTER TABLE `classroom_member` CHANGE `lastLearnTime` `lastLearnTime` int(10)  DEFAULT '0' COMMENT '最后学习时间';",
            ],
            [
                'table' => 'course_member',
                'column' => 'lastLearnTime',
                'action' => 'modify',
                'sql' => "ALTER TABLE `course_member` CHANGE `lastLearnTime` `lastLearnTime` int(10)  DEFAULT '0' COMMENT '最后学习时间';"
            ],
        ];
        if (isset($sqls[$page - 1])) {
            $sql = $sqls[$page - 1];
            if ($sql['action'] === 'add_column') {
                if (!$this->isFieldExist($sql['table'], $sql['column'])) {
                    $this->getConnection()->exec($sql['sql']);
                }
            }

            if($sql['action'] === 'modify') {
                $this->getConnection()->exec($sql['sql']);
            }
            $this->logger('info', "执行数据库字段升级脚本，行为：「{$sql['action']}」, 语句： 「{$sql['sql']}」");
            return $page + 1;
        }
        return 1;
    }

    public function freshClassroomMemberLastLearnTimeData()
    {
        $sql = "UPDATE `classroom_member` SET lastLearnTime = '0' WHERE lastLearnTime IS NULL;";
        $this->getConnection()->exec($sql);
        $this->logger('info', "执行数据升级脚本，行为：「freshClassroomMemberLastLearnTimeData」, 语句： 「{$sql}」");
        return 1;
    }

    public function freshCourseMemberLastLearnTimeData()
    {
        $sql = "UPDATE `course_member` SET lastLearnTime = '0' WHERE lastLearnTime IS NULL;";
        $this->getConnection()->exec($sql);
        $this->logger('info', "执行数据升级脚本，行为：「freshCourseMemberLastLearnTimeData」, 语句： 「{$sql}」");
        return 1;
    }

    public function freshCourseMemberIsLearned0Data()
    {
        $sql = "UPDATE `course_member` SET isLearned = 1 WHERE finishedTime > 0 AND isLearned = 0;";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function freshCourseMemberIsLearned1Data()
    {
        $sql = "UPDATE `course_member` SET isLearned = 0 WHERE finishedTime = 0 AND isLearned = 1;";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function refreshClassroomMemberFinishedData($page)
    {
        $perPageCount = 500;
        $classroomMembersCount = $this->getConnection()->fetchColumn("SELECT COUNT(*) FROM `classroom_member` WHERE role LIKE '%|student|%';");
        $start =($page - 1) * $perPageCount;
        if ($start >= $classroomMembersCount) {
            return 1;
        }
        $classroomMemberIds = array_column($this->getConnection()->fetchAll("SELECT id FROM `classroom_member` WHERE role LIKE '%|student|%' ORDER BY id ASC LIMIT {$start},{$perPageCount};"), 'id');
        if (empty($classroomMemberIds)) {
            return $page + 1;
        }
        $marks = str_repeat('?,', count($classroomMemberIds) - 1).'?';
        $sql = "SELECT cmo.id as id, cmn.isFinished AS isFinished, cmn.finishedTime AS finishedTime FROM `classroom_member` cmo INNER JOIN 
                    (SELECT cm.`classroomId` AS classroomId, 
                        cm.`userId` AS userId,
                        (CASE min(coursem.`isLearned`) = 0 WHEN TRUE THEN 0 ELSE 1 END) AS isFinished, 
                        (CASE min(coursem.`isLearned`) = 1 WHEN TRUE THEN max(coursem.`finishedTime`) ELSE 0 END) AS finishedTime 
                    FROM `classroom_member` cm 
                    INNER JOIN `course_member` coursem 
                    ON cm.`classroomId` = coursem.`classroomId` AND cm.`userId` = coursem.`userId`
                    WHERE cm.`role` LIKE '%|student|%' AND cm.`id` IN ({$marks})
                    GROUP BY cm.`classroomId`,cm.userId) cmn
                ON cmo.classroomId = cmn.classroomId AND cmo.userId = cmn.userId WHERE cmo.`role` LIKE '%|student|%';";

        $updateFields = $this->getConnection()->fetchAll($sql, $classroomMemberIds);
        if (empty($updateFields)) {
            return $page + 1;
        }
        $this->getClassroomMemberDao()->batchUpdate(array_column($updateFields, 'id'), $updateFields, 'id');
        $idsString = json_encode(array_column($updateFields, 'id'));
        $this->logger('info', "更新classroom_member的finishedData, 分页：{$page}，此次更新的id有： {$idsString}");
        return $page + 1;
    }

    /**
     * @return \Biz\System\Service\CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return \Biz\Role\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getTableCount($table)
    {
        $sql = "select count(*) from `{$table}`;";

        return $this->getConnection()->fetchColumn($sql) ?: 0;
    }

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;

        return array($step, $page);
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

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:ClassroomDao');
    }

    /**
     * @return \Biz\Course\Dao\CourseMemberDao
     */
    protected function getCourseMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    protected function getSignUserStatisticsDao()
    {
        return $this->createDao('Sign:SignUserStatisticsDao');
    }

    /**
     * @return \Biz\Classroom\Dao\ClassroomMemberDao
     */
    protected function getClassroomMemberDao()
    {
        return $this->createDao('Classroom:ClassroomMemberDao');
    }
}

abstract class AbstractUpdater
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getConnection()
    {
        return $this->biz['db'];
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    abstract public function update();

    protected function logger($level, $message)
    {
        $version = \AppBundle\System::VERSION;
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
    }

    /**
     * @return \Biz\DiscoveryColumn\Service\DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return \Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\System\Service\H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
