<?php

use Phpmig\Migration\Migration;

class CourseV8AddDrainage extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_v8` ADD COLUMN `drainage` text COMMENT '引流设置'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_v8` DROP COLUMN `drainage`');
    }
}
