<?php

use Phpmig\Migration\Migration;

class UserAddScrmUuid extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `user` ADD COLUMN `scrmUuid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Scrm平台用户Uuid' AFTER uuid;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `user` DROP COLUMN `scrmUuid`;");
    }
}
