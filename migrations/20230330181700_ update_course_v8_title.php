<?php

use Phpmig\Migration\Migration;

class UpdateCourseV8Title extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            UPDATE `course_v8` SET title='默认计划' WHERE title='';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
