<?php

use Phpmig\Migration\Migration;

class TestpaperV8AddQuestionTypeSeq extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `testpaper_v8` ADD COLUMN `questionTypeSeq` varchar(64) NOT NULL DEFAULT '' COMMENT '题型排序' AFTER itemCount;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `testpaper_v8` DROP COLUMN `questionTypeSeq`;');
    }
}
