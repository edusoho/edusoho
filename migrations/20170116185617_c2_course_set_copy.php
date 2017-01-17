<?php

use Phpmig\Migration\Migration;

class C2CourseSetCopy extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
             ALTER TABLE `c2_course_set` ADD COLUMN  `parentId` int(10) DEFAULT 0 COMMENT '课程父id';
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
             ALTER TABLE `c2_course_set` DROP COLUMN  `parentId`;
        ");
    }
}
