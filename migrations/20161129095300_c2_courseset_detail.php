<?php

use Phpmig\Migration\Migration;

class C2CoursesetDetail extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course_set ADD COLUMN summary TEXT;
            ALTER TABLE c2_course_set ADD COLUMN goals TEXT;
            ALTER TABLE c2_course_set ADD COLUMN audiences TEXT;
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
            ALTER TABLE c2_course_set DROP COLUMN summary;
            ALTER TABLE c2_course_set DROP COLUMN goals;
            ALTER TABLE c2_course_set DROP COLUMN audiences;
        ");
    }
}
