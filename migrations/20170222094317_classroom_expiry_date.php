<?php

use Phpmig\Migration\Migration;

class ClassroomExpiryDate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `classroom` ADD `expiryMode` enum('date', 'days', 'none') NOT NULL DEFAULT 'none' COMMENT '有效期的模式'; 
        ");
        $db->exec("
            ALTER TABLE `classroom` ADD `expiryDay` int(10) NOT NULL DEFAULT '0' COMMENT 
            '有效天数';
        ");
        $db->exec("
            ALTER TABLE `classroom` ADD `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间'; 
        ");
        $db->exec("
            ALTER TABLE `classroom_member` ADD `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间'; 
        ");

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE `classroom` DROP COLUMN `expiryDay`");
        $db->exec("ALTER TABLE `classroom` DROP COLUMN `expiryMode`");
        $db->exec("ALTER TABLE `classroom` DROP COLUMN `deadline`");
        $db->exec("ALTER TABLE `classroom_member` DROP COLUMN `deadline`");
    }
}
