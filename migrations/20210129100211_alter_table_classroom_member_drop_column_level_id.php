<?php

use Phpmig\Migration\Migration;

class AlterTableClassroomMemberDropColumnLevelId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `classroom_member` DROP `levelId`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
