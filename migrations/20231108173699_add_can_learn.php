<?php

use Phpmig\Migration\Migration;

class AddCanLearn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_set_v8` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `creator`;
            ALTER TABLE `course_v8` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `publishLessonNum`;
            ALTER TABLE `item_bank_exercise` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `creator`;
            ALTER TABLE `classroom` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `income`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `course_set_v8` DROP COLUMN `canLearn`;
            ALTER TABLE `course_v8` DROP COLUMN `canLearn`;
            ALTER TABLE `item_bank_exercise` DROP COLUMN `canLearn`;
            ALTER TABLE `classroom` DROP COLUMN `canLearn`;
        ');
    }
}
