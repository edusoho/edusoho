<?php

use Phpmig\Migration\Migration;

class AlterCourseMaterialV8 extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_material_v8` modify COLUMN `fileSize` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec(" 
            ALTER TABLE `course_material_v8` modify COLUMN `fileSize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小';
        ");
    }
}
