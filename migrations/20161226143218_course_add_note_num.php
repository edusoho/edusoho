<?php

use Phpmig\Migration\Migration;

class CourseAddNoteNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` ADD noteNum INT(10) UNSIGNED NOT NULL DEFAULT 0;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `noteNum`;");
    }
}
