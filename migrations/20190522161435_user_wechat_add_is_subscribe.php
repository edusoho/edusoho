<?php

use Phpmig\Migration\Migration;

class UserWechatAddIsSubscribe extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `user_wechat` ADD COLUMN `isSubscribe` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否订阅服务号' AFTER `unionId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `user_wechat` DROP COLUMN `isSubscribe`;');
    }
}
