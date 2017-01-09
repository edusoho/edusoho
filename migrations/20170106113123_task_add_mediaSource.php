<?php

use Phpmig\Migration\Migration;

class TaskAddMediaSource extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `course_task` ADD COLUMN `mediaSource` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)';
            UPDATE course_task  SET `mediaSource` = 'self'  WHERE TYPE = 'video';
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
            ALTER TABLE `course_task` DROP COLUMN `mediaSource`;
        ");
    }
}
