<?php

use Phpmig\Migration\Migration;

class UpdateCopiedCourse extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            UPDATE `course_v8`, `course_v8` c2 SET course_v8.parentId=0 WHERE course_v8.parentId = c2.id AND course_v8.parentId > 0 AND course_v8.courseSetId=c2.courseSetId;
        ');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
