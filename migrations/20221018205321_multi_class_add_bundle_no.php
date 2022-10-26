<?php

use Phpmig\Migration\Migration;

class MultiClassAddBundleNo extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `multi_class` ADD COLUMN `bundle_no` int(10) NOT NULL DEFAULT 0 COMMENT '大组no' AFTER `courseId`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `multi_class` DROP COLUMN `bundle_no`;");
    }
}
