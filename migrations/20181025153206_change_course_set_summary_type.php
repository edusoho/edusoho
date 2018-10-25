<?php

use Phpmig\Migration\Migration;

class ChangeCourseSetSummaryType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `course_set_v8` MODIFY COLUMN `summary` longtext;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_set_v8` MODIFY COLUMN `summary` text;");
    }
}
