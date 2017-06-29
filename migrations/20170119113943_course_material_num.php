<?php

use Phpmig\Migration\Migration;

class CourseMaterialNum extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
            UPDATE `c2_course` ce , (SELECT count(id) AS num , courseId FROM `course_material` GROUP BY courseId) cm  SET ce.`materialNum` = cm.num  WHERE ce.id = cm.courseId;
            UPDATE `c2_course_set` ce , (SELECT count(id) AS num , courseSetId FROM `course_material` GROUP BY courseSetId) cm  SET ce.`materialNum` = cm.num  WHERE ce.id = cm.`courseSetId`;
        ');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
