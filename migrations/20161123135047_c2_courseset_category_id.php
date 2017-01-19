<?php

use Phpmig\Migration\Migration;

class C2CoursesetCategoryId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course_set CHANGE categories categoryId int(10) NOT NULL DEFAULT '0';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
