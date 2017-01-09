<?php

use Phpmig\Migration\Migration;

class CourseSetAddNoteNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `c2_course_set` ADD COLUMN  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程笔记数';
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
            ALTER TABLE `c2_course_set` DROP COLUMN  `noteNum`;
        ");
    }
}
