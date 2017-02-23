<?php

use Phpmig\Migration\Migration;

class ActivityAddCopyId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity` ADD COLUMN `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制来源activity的id';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity` DROP COLUMN `copyId`;");
    }
}
