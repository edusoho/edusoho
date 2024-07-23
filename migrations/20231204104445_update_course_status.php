<?php

use Phpmig\Migration\Migration;

class UpdateCourseStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            update course_set_v8 set status = 'unpublished' where status = 'closed';
            update course_v8 set status = 'unpublished' where status = 'closed';
            update item_bank_exercise set status = 'unpublished' where status = 'closed';
            update classroom set status = 'unpublished' where status = 'closed';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            update course_set_v8 set status = 'closed' where status = 'unpublished';
            update course_v8 set status = 'closed' where status = 'unpublished';
            update item_bank_exercise set status = 'closed' where status = 'unpublished';
            update classroom set status = 'closed' where status = 'unpublished';
        ");
    }
}
