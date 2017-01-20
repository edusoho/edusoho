<?php

use Phpmig\Migration\Migration;

class AlterCourseFavorite extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_favorite` CHANGE `courseSetId` `courseSetId` INT(10) NOT NULL DEFAULT '0' COMMENT '课程ID';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
