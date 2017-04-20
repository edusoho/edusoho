<?php

use Phpmig\Migration\Migration;

class ClassroomAddCreator extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE classroom ADD COLUMN `creator` int(10) NOT NULL DEFAULT 0 COMMENT '班级创建者';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE classroom DROP COLUMN `creator`;
        ');
    }
}
