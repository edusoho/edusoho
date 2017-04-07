<?php

use Phpmig\Migration\Migration;

class CourseAddHitNum extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_v8` ADD COLUMN `hitNum` int(10) NOT NULL DEFAULT '0' COMMENT '点击量';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_v8` DROP COLUMN `hitNum`;');
    }
}
