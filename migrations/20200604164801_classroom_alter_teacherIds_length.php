<?php

use Phpmig\Migration\Migration;

class ClassroomAlterTeacherIdsLength extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `classroom` modify COLUMN `teacherIds` varchar(1024) NOT NULL DEFAULT '' COMMENT '教师IDs';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `classroom` modify COLUMN `teacherIds` varchar(255) NOT NULL DEFAULT '' COMMENT '教师IDs';");
    }
}
