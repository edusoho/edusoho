<?php

use Phpmig\Migration\Migration;

class ChangeLessonIdToTaskId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
          ALTER TABLE `question_marker_result` CHANGE `lessonId` `taskId` INT(10) UNSIGNED NOT NULL DEFAULT '0';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
