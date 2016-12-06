<?php

use Phpmig\Migration\Migration;

class C2CourseTaskStudentCount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course ADD COLUMN taskCount int(11) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN studentCount int(11) DEFAULT 0;
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
            ALTER TABLE c2_course DROP COLUMN taskCount;
            ALTER TABLE c2_course DROP COLUMN studentCount;
        ");
    }
}
