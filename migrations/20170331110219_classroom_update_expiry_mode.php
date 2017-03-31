<?php

use Phpmig\Migration\Migration;

class ClassroomUpdateExpiryMode extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE `classroom` CHANGE `expiryMode` `expiryMode` VARCHAR(32) NOT NULL DEFAULT 'forever' COMMENT '学习有效期模式：date、days、forever';
            UPDATE `classroom` SET expiryMode='forever' WHERE expiryMode='none';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            UPDATE classroom SET expiryMode='none' WHERE expiryMode='forever';
            ALTER TABLE `classroom` CHANGE `expiryMode` `expiryMode` enum('date','days','none') NOT NULL DEFAULT 'none' COMMENT '有效期的模式';
        ");
    }
}
