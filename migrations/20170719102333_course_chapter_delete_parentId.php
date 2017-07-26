<?php

use Phpmig\Migration\Migration;

class CourseChapterDeleteParentId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `course_chapter` DROP `parentId`');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
