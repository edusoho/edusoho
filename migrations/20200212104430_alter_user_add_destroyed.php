<?php

use Phpmig\Migration\Migration;

class AlterUserAddDestroyed extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("ALTER TABLE `user` ADD `destroyed` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否注销';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `user` DROP column `destroyed`;');
    }
}
