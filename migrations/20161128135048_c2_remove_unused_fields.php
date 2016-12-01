<?php

use Phpmig\Migration\Migration;

class C2RemoveUnusedFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course_set DROP COLUMN deleted;
            ALTER TABLE c2_course_set DROP COLUMN smallPicture;
            ALTER TABLE c2_course_set DROP COLUMN middlePicture;
            ALTER TABLE c2_course_set DROP COLUMN largePicture;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
