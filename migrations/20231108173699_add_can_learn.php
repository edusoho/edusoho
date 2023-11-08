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
            ALTER TABLE `course_set_v8` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否可学' AFTER `showable`;
            UPDATE `course_set_v8` set canLearn = 1 where status = 'published';
            ALTER TABLE `course_v8` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否可学' AFTER `showable`;
            UPDATE `course_v8` set canLearn = 1 where status = 'published';
            ALTER TABLE `item_bank_exercise` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否可学' AFTER `showable`;
            UPDATE `item_bank_exercise` set canLearn = 1 where status = 'published';
            ALTER TABLE `classroom` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否可学' AFTER `showable`;
            UPDATE `classroom` set canLearn = 1 where status = 'published';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_set_v8` DROP COLUMN `canLearn`;
            ALTER TABLE `course_v8` DROP COLUMN `canLearn`;
            ALTER TABLE `item_bank_exercise` DROP COLUMN `canLearn`;
            ALTER TABLE `classroom` DROP COLUMN `canLearn`;
        ");
    }
}
