<?php

use Phpmig\Migration\Migration;

class C2CourseCopyId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course ADD COLUMN copyCourseId int(11) NOT NULL DEFAULT 0
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
            ALTER TABLE c2_course DROP COLUMN copyCourseId
        ");
    }
}
