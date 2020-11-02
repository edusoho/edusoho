<?php

use Phpmig\Migration\Migration;

class UpdateClassroomTeacherIdsSize extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `classroom` CHANGE `teacherIds` `teacherIds` VARCHAR(4096) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '教师IDs';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
