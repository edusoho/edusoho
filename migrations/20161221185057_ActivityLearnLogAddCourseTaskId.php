<?php

use Phpmig\Migration\Migration;

class ActivityLearnLogAddCourseTaskId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {

        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
           ALTER TABLE `activity_learn_log` ADD COLUMN `courseTaskId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '教学活动id';
        ");


    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `activity_learn_log` DROP COLUMN `courseTaskId`;
            ");

    }
}
