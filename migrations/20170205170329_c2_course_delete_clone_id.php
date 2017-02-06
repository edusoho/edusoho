<?php

use Phpmig\Migration\Migration;

class C2CourseDeleteCloneId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course DROP COLUMN cloneId
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
            ALTER TABLE c2_course ADD COLUMN cloneId int(11) NOT NULL DEFAULT 0
        ");
    }
}
