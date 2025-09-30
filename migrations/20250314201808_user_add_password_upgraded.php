<?php

use Phpmig\Migration\Migration;

class UserAddPasswordUpgraded extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `user` ADD COLUMN `passwordUpgraded` tinyint(1)  NOT NULL DEFAULT 0 COMMENT '是否已升级密码'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `user` DROP COLUMN `passwordUpgraded`');
    }
}
