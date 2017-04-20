<?php

use Phpmig\Migration\Migration;

class CourseMaterialAddCourseSetId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE course_material ADD COLUMN courseSetId int(10) default 0 COMMENT '课程ID';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
            ALTER TABLE course_material DROP COLUMN courseSetId;
        ');
    }
}
