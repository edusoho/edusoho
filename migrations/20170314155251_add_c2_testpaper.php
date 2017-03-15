<?php

use Phpmig\Migration\Migration;

class AddC2Testpaper extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
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
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
        ");

        //以下仅供cours2.0开发使用
        if ($this->isFieldExist('testpaper', 'courseSetId')) {
            $biz['db']->exec('
                INSERT INTO c2_testpaper (
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
                    courseSetId
                ) SELECT
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
                    courseSetId FROM testpaper
                    WHERE id NOT IN (SELECT `id` FROM `c2_testpaper`);
            ');

            $biz['db']->exec('
                INSERT INTO c2_testpaper_item (
                    id,
                    testId,
                    seq,
                    questionId,
                    questionType,
                    parentId,
                    score,
                    missScore,
                    copyId
                ) SELECT
                    id,
                    testId,
                    seq,
                    questionId,
                    questionType,
                    parentId,
                    score,
                    missScore,
                    copyId FROM testpaper_item
                WHERE id NOT IN (SELECT `id` FROM `c2_testpaper_item`)
            ');

            $biz['db']->exec('
                INSERT INTO c2_testpaper_result(
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
                    type,
                    courseSetId
                ) SELECT
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
                    type,
                    courseSetId
                FROM testpaper_result WHERE id NOT IN (SELECT id FROM c2_testpaper_result)
            ');

            $biz['db']->exec('
                INSERT INTO c2_testpaper_item_result (
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
                    pId
                ) SELECT
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
                    pId
                FROM testpaper_item_result WHERE id NOT IN (SELECT id FROM c2_testpaper_item_result)
            ');
        }
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec(' drop table c2_testpaper');
        $biz['db']->exec(' drop table c2_testpaper_item');
        $biz['db']->exec(' drop table c2_testpaper_result');
        $biz['db']->exec(' drop table c2_testpaper_item_result');
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
