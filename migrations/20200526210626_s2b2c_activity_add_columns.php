<?php

use Phpmig\Migration\Migration;

class S2b2cActivityAddColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `activity_homework` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_exercise` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `activity_homework` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_exercise` DROP `syncId`;');
    }
}
