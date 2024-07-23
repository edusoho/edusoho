<?php

use Phpmig\Migration\Migration;

class CreateTableBizAnswerRandomSeqRecord extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `biz_answer_random_seq_record` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `answer_record_id` INT(10) unsigned NOT NULL COMMENT '答题记录id',
              `items_random_seq` MEDIUMTEXT COMMENT '题目随机顺序，json结构，key是section的id，value是section内试题item_id的顺序列表',
              `options_random_seq` MEDIUMTEXT COMMENT '选项随机顺序，json结构，key是question的id，value是选项的顺序列表',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `answer_record_id` (`answer_record_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        if (!$this->isFieldExist('biz_answer_scene', 'end_time')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开考截止时间 0表示不限制' after `start_time`;");
        }
        if (!$this->isFieldExist('biz_answer_scene', 'is_items_seq_random')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `is_items_seq_random` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启题目乱序' after `end_time`;");
        }
        if (!$this->isFieldExist('biz_answer_scene', 'is_options_seq_random')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `is_options_seq_random` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启选项乱序' after `is_items_seq_random`;");
        }
        if (!$this->isFieldExist('biz_answer_record', 'is_items_seq_random')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `is_items_seq_random` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启题目乱序';");
        }
        if (!$this->isFieldExist('biz_answer_record', 'is_options_seq_random')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `is_options_seq_random` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启选项乱序';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `biz_answer_random_seq_record`;');
        if ($this->isFieldExist('biz_answer_scene', 'end_time')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_scene` DROP COLUMN `end_time`;");
        }
        if ($this->isFieldExist('biz_answer_scene', 'is_items_seq_random')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_scene` DROP COLUMN `is_items_seq_random`;");
        }
        if ($this->isFieldExist('biz_answer_scene', 'is_options_seq_random')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_scene` DROP COLUMN `is_options_seq_random`;");
        }
        if ($this->isFieldExist('biz_answer_record', 'is_items_seq_random')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_record` DROP COLUMN `is_items_seq_random`;");
        }
        if ($this->isFieldExist('biz_answer_record', 'is_options_seq_random')) {
            $biz['db']->exec("ALTER TABLE `biz_answer_record` DROP COLUMN `is_options_seq_random`;");
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
