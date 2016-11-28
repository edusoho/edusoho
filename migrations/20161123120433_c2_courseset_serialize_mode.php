<?php

use Phpmig\Migration\Migration;

class C2CoursesetSerializeMode extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course_set ADD COLUMN serializeMode varchar(32) NOT NULL DEFAULT 'none' COMMENT 'none, serilized, finished'
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
            ALTER TABLE c2_course DROP COLUMN serializeMode
        ");
    }
}
