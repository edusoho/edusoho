<?php

use Phpmig\Migration\Migration;

class AlterUserAddScrmStaffId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `user` ADD COLUMN `scrmStaffId` int(11) NOT NULL DEFAULT '0' COMMENT 'Scrm平台员工ID' AFTER scrmUuid;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `user` DROP COLUMN `scrmStaffId`;");
    }
}
