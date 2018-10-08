<?php

use Phpmig\Migration\Migration;

class UserAddFaceRegister extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `user` ADD `faceRegistered` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否人脸注册过';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `user` DROP COLUMN `faceRegistered`;');
    }
}
