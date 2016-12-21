<?php

use Phpmig\Migration\Migration;

class C2CourseAddTeacherIds extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course ADD COLUMN teacherIds VARCHAR(1024) DEFAULT 0 COMMENT '可见教师ID列表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE `c2_course` DROP COLUMN `teacherIds`;");
    }
}
