<?php

use Phpmig\Migration\Migration;

class BuyExpireTimeIndex extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `course` CHANGE `buyExpireTime` `buyExpiryTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买开放有效期'");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `course` DROP `buyExpireTime`');
    }
}
