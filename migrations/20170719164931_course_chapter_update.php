<?php

use Phpmig\Migration\Migration;

class CourseChapterUpdate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `course_chapter` CHANGE `number` `number` INT(10) UNSIGNED NOT NULL DEFAULT \'1\' COMMENT \'章节编号\';');
        $db->exec('ALTER TABLE `course_chapter` CHANGE `seq` `seq` INT(10) UNSIGNED NOT NULL DEFAULT \'1\' COMMENT \'章节序号\';');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
