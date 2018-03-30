<?php

use Phpmig\Migration\Migration;

class ClassroomAddHotSeq extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `classroom` ADD `hotSeq` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最热班级排序' AFTER `ratingNum`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            ALTER TABLE `classroom` DROP COLUMN `hotSeq`;
        ');
    }
}
