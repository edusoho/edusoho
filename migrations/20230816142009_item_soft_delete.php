<?php

use Phpmig\Migration\Migration;

class ItemSoftDelete extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `biz_assessment_snapshot` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `origin_assessment_id` INT(10) unsigned NOT NULL COMMENT '原试卷id',
              `snapshot_assessment_id` INT(10) unsigned NOT NULL COMMENT '快照试卷id',
              `sections_snapshot` text COMMENT '原section和快照section对应关系',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `origin_assessment_id` (`origin_assessment_id`),
              KEY `snapshot_assessment_id` (`snapshot_assessment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        if (!$this->isFieldExist('biz_answer_record', 'exercise_mode')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `exercise_mode` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '练习模式 0测试模式 1一题一答' AFTER `exam_mode`;");
        }
        if (!$this->isFieldExist('biz_item_category', 'seq')) {
            $biz['db']->exec("ALTER TABLE `biz_item_category` ADD COLUMN `seq` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '同一父分类下分类排序' AFTER `weight`;");
        }
        if (!$this->isFieldExist('item_bank_exercise', 'hiddenChapterIds')) {
            $biz['db']->exec("ALTER TABLE `item_bank_exercise` ADD COLUMN `hiddenChapterIds` TEXT COMMENT '不发布的章节(题目分类)id序列' AFTER `teacherIds`;");
        }
        if (!$this->isFieldExist('biz_item', 'is_deleted')) {
            $biz['db']->exec("ALTER TABLE `biz_item` ADD COLUMN `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否删除';");
        }
        if (!$this->isFieldExist('biz_item', 'deleted_time')) {
            $biz['db']->exec("ALTER TABLE `biz_item` ADD COLUMN `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间';");
        }
        if (!$this->isFieldExist('biz_item_attachment', 'is_deleted')) {
            $biz['db']->exec("ALTER TABLE `biz_item_attachment` ADD COLUMN `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否删除';");
        }
        if (!$this->isFieldExist('biz_item_attachment', 'deleted_time')) {
            $biz['db']->exec("ALTER TABLE `biz_item_attachment` ADD COLUMN `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间';");
        }
        if (!$this->isFieldExist('biz_question', 'is_deleted')) {
            $biz['db']->exec("ALTER TABLE `biz_question` ADD COLUMN `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否删除';");
        }
        if (!$this->isFieldExist('biz_question', 'deleted_time')) {
            $biz['db']->exec("ALTER TABLE `biz_question` ADD COLUMN `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间';");
        }
        $biz['db']->exec('ALTER TABLE `biz_question_favorite` ADD INDEX `item_id` (`item_id`);');
        if (!$this->isFieldExist('activity_homework', 'assessmentBankId')) {
            $biz['db']->exec("ALTER TABLE `activity_homework` ADD COLUMN `assessmentBankId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '试卷所属题库id' AFTER `assessmentId`;");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `biz_assessment_snapshot`;');

        if ($this->isFieldExist('biz_answer_record', 'exercise_mode')) {
            $biz['db']->exec('ALTER TABLE `biz_answer_record` DROP COLUMN `exercise_mode`;');
        }
        if ($this->isFieldExist('biz_item_category', 'seq')) {
            $biz['db']->exec('ALTER TABLE `biz_item_category` DROP COLUMN `seq`;');
        }
        if ($this->isFieldExist('item_bank_exercise', 'hiddenChapterIds')) {
            $biz['db']->exec('ALTER TABLE `item_bank_exercise` DROP COLUMN `hiddenChapterIds`;');
        }
        if ($this->isFieldExist('biz_item', 'is_deleted')) {
            $biz['db']->exec('ALTER TABLE `biz_item` DROP COLUMN `is_deleted`;');
        }
        if ($this->isFieldExist('biz_item', 'deleted_time')) {
            $biz['db']->exec('ALTER TABLE `biz_item` DROP COLUMN `deleted_time`;');
        }
        if ($this->isFieldExist('biz_item_attachment', 'is_deleted')) {
            $biz['db']->exec('ALTER TABLE `biz_item_attachment` DROP COLUMN `is_deleted`;');
        }
        if ($this->isFieldExist('biz_item_attachment', 'deleted_time')) {
            $biz['db']->exec('ALTER TABLE `biz_item_attachment` DROP COLUMN `deleted_time`;');
        }
        if ($this->isFieldExist('biz_question', 'is_deleted')) {
            $biz['db']->exec('ALTER TABLE `biz_question` DROP COLUMN `is_deleted`;');
        }
        if ($this->isFieldExist('biz_question', 'deleted_time')) {
            $biz['db']->exec('ALTER TABLE `biz_question` DROP COLUMN `deleted_time`;');
        }
        if ($this->isFieldExist('activity_homework', 'assessmentBankId')) {
            $biz['db']->exec('ALTER TABLE `activity_homework` DROP COLUMN `assessmentBankId`;');
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return !empty($result);
    }
}
