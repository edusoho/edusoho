<?php

use Phpmig\Migration\Migration;

class AddTestpaperV8 extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();

        if (!$this->isTableExist('c2_testpaper')) {
            $biz['db']->exec("
                CREATE TABLE IF NOT EXISTS `testpaper_v8` (
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
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

                CREATE TABLE IF NOT EXISTS `testpaper_item_v8` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目',
                  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
                  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
                  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
                  `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
                  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
                  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
                  `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
                  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源testpaper_item的id',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

                CREATE TABLE IF NOT EXISTS `testpaper_result_v8` (
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
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

                CREATE TABLE IF NOT EXISTS `testpaper_item_result_v8` (
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
                  PRIMARY KEY (`id`),
                  KEY `testPaperResultId` (`resultId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        //以下仅供cours2.0开发使用
        if ($this->isTableExist('c2_testpaper')) {
            $biz['db']->exec('
                ALTER TABLE `c2_testpaper` RENAME TO `testpaper_v8`;
                ALTER TABLE `c2_testpaper_item` RENAME TO `testpaper_item_v8`;
                ALTER TABLE `c2_testpaper_result` RENAME TO `testpaper_result_v8`;
                ALTER TABLE `c2_testpaper_item_result` RENAME TO `testpaper_item_result_v8`;
            ');
        }

        if ($this->isTableExist('testpaper_v8')) {
            $biz['db']->exec("
                ALTER TABLE testpaper_v8 ADD `migrateTestId` int(11) unsigned NOT NULL DEFAULT '0';
                ALTER TABLE testpaper_item_v8 ADD `migrateItemId` int(11) unsigned NOT NULL DEFAULT '0';
                ALTER TABLE testpaper_item_v8 ADD `migrateType` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型';

                ALTER TABLE testpaper_result_v8 ADD `migrateResultId` int(11) unsigned NOT NULL DEFAULT '0';
                ALTER TABLE testpaper_item_result_v8 ADD `migrateItemResultId` int(11) unsigned NOT NULL DEFAULT '0';
                ALTER TABLE testpaper_item_result_v8 ADD`migrateType` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型';

            ");
        }
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec(' drop table testpaper_v8');
        $biz['db']->exec(' drop table testpaper_item_v8');
        $biz['db']->exec(' drop table testpaper_result_v8');
        $biz['db']->exec(' drop table testpaper_item_result_v8');
    }

    protected function isTableExist($table)
    {
        $biz = $this->getContainer();
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
