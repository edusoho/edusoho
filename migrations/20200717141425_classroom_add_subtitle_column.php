<?php

use Phpmig\Migration\Migration;

class ClassroomAddSubtitleColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `classroom` ADD COLUMN `subtitle` varchar(1024) DEFAULT '' COMMENT '班级副标题' AFTER `title`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `classroom` DROP COLUMN `subtitle`;');
    }
}
