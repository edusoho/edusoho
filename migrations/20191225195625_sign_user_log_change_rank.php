<?php

use Phpmig\Migration\Migration;

class SignUserLogChangeRank extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `sign_user_log` CHANGE `rank` `_rank` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到排名';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `sign_user_log` CHANGE `_rank` `rank` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到排名';");
    }
}
