<?php

use Phpmig\Migration\Migration;

class CourseAddCover extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course ADD COLUMN cover VARCHAR(1024);
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course DROP COLUMN cover;
        ");
    }
}
