<?php

use Phpmig\Migration\Migration;

class S2b2cCourseTaskAddSyncid extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        // 当前批量创建课程任务，
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
        $connection->exec("ALTER TABLE `activity_flash` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `activity_live` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `upload_files` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        $connection->exec("ALTER TABLE `course_material_v8` ADD `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");

    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
