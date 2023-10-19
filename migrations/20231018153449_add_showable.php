<?php

use Phpmig\Migration\Migration;

class AddShowable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_set_v8` ADD COLUMN `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示' AFTER `platform`;
            ALTER TABLE `course_v8` ADD COLUMN `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示' AFTER `platform`;
            ALTER TABLE `item_bank_exercise` ADD COLUMN `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示' AFTER `isFree`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `course_set_v8` DROP COLUMN `showable`;
            ALTER TABLE `course_v8` DROP COLUMN `showable`;
            ALTER TABLE `item_bank_exercise` DROP COLUMN `showable`;
        ');
    }
}
