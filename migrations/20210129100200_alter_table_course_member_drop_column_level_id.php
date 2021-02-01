<?php

use Phpmig\Migration\Migration;

class AlterTableCourseMemberDropColumnLevelId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_member` DROP `levelId`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
