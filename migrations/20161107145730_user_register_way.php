<?php

use Phpmig\Migration\Migration;

class UserRegisterWay extends Migration
{
    private function getDb()
    {
        $biz = $this->getContainer();

        return $biz['db'];
    }

    /**
     * Do the migration.
     */
    public function up()
    {
        $db = $this->getDb();

        $db->exec("ALTER TABLE `user` ADD `registeredWay` varchar(64) NOT NULL DEFAULT '' COMMENT '注册设备来源(web/ios/android)'");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $db = $this->getDb();

        $db->exec('ALTER TABLE `user` DROP COLUMN `registeredWay`');
    }
}
