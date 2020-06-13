<?php

use Phpmig\Migration\Migration;

class DeleteUnusedTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE IF EXISTS `course`;
            DROP TABLE IF EXISTS `course_material`;
            DROP TABLE IF EXISTS `testpaper`;
            DROP TABLE IF EXISTS `testpaper_item`;
            DROP TABLE IF EXISTS `testpaper_result`;
            DROP TABLE IF EXISTS `testpaper_item_result`;
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
