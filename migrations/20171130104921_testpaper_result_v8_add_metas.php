<?php

use Phpmig\Migration\Migration;

class TestpaperResultV8AddMetas extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `testpaper_result_v8` ADD COLUMN `metas` text COMMENT '练习的题型排序等附属信息' AFTER `updateTime`; ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `testpaper_result_v8` DROP COLUMN `metas`;');
    }
}
