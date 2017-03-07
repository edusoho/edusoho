<?php

use Phpmig\Migration\Migration;

class FavoriteAddCourseSetId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE course_favorite ADD courseSetId INT(10) NOT NULL DEFAULT 0 COMMENT '课程ID';");
        $biz['db']->exec("ALTER TABLE course_favorite MODIFY courseId INT(10) unsigned NOT NULL COMMENT '教学计划ID';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE course_favorite DROP COLUMN courseSetId;');
        $db->exec("ALTER TABLE course_favorite MODIFY courseId INT(10) unsigned NOT NULL COMMENT '收藏课程的ID';");
    }
}
