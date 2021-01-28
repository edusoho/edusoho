<?php

use Phpmig\Migration\Migration;

class AddCourseV8Seq extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `course_v8` ADD COLUMN `seq` int(10)  NOT NULL DEFAULT 0 COMMENT '排序序号' AFTER `status`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `course_v8` DROP COLUMN `seq`;');
    }
}
