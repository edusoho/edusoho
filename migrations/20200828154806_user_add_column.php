<?php

use Phpmig\Migration\Migration;

class UserAddColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `user` ADD passwordChanged tinyint(1) NOT NULL default 0 COMMENT '是否修改密码';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `user` DROP COLUMN `passwordChanged`;');
    }
}
