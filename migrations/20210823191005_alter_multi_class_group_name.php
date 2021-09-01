<?php

use Phpmig\Migration\Migration;

class AlterMultiClassGroupName extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `multi_class_group` ADD COLUMN `seq` int(10) NOT NULL DEFAULT 0 COMMENT '分组序号' AFTER `name`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `multi_class_group` DROP COLUMN `seq`;');
    }
}
