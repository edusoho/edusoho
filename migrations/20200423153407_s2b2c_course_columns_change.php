<?php

use Phpmig\Migration\Migration;

class S2b2cCourseColumnsChange extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `course_set_v8` 
                ADD `platform` varchar(32) NOT NULL DEFAULT 'self' COMMENT '课程来源平台：self 自己平台创建，supplier S端提供';
        ");

        $connection->exec("
            ALTER TABLE `course_v8` 
                ADD `platform` varchar(32) NOT NULL DEFAULT 'self' COMMENT '课程来源平台：self 自己平台创建，supplier S端提供';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `course_set_v8` DROP COLUMN `platform`;');
        $connection->exec('ALTER TABLE `course_v8` DROP COLUMN `platform`;');
    }
}
