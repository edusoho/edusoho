<?php

use Phpmig\Migration\Migration;

class S2b2cAddSyncid extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `course_task` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_download` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_video` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_audio` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_doc` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_ppt` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_flash` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_text` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_testpaper` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_live` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `upload_files` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `course_material_v8` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `testpaper_v8` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `testpaper_item_v8` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `question` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `course_chapter` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `file_used` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `course_task` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_download` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_video` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_audio` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_doc` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_ppt` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_flash` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_text` DROP `syncId`');
        $connection->exec('ALTER TABLE `activity_testpaper` DROP `syncId`;');
        $connection->exec('ALTER TABLE `activity_live` DROP `syncId`;');
        $connection->exec('ALTER TABLE `upload_files` DROP `syncId`;');
        $connection->exec('ALTER TABLE `course_material_v8` DROP `syncId`;');
        $connection->exec('ALTER TABLE `testpaper_v8` DROP `syncId`;');
        $connection->exec('ALTER TABLE `testpaper_item_v8` DROP `syncId`;');
        $connection->exec('ALTER TABLE `question` DROP `syncId`;');
        $connection->exec('ALTER TABLE `course_chapter` DROP `syncId`;');
        $connection->exec('ALTER TABLE `file_used` DROP `syncId`;');
    }
}
