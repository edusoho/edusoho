<?php

use Phpmig\Migration\Migration;

class UserWechatAddColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `user_wechat` ADD COLUMN `nickname` varchar(512) NOT NULL DEFAULT '' COMMENT '微信昵称' AFTER `isSubscribe`;");
        $connection->exec("ALTER TABLE `user_wechat` ADD COLUMN `profilePicture` varchar(256) NOT NULL DEFAULT '' COMMENT '微信头像' AFTER `nickname`;");
        $connection->exec("ALTER TABLE `user_wechat` ADD COLUMN `subscribeTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关注时间' AFTER `lastRefreshTime`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `user_wechat` DROP COLUMN `nickname`;');
        $connection->exec('ALTER TABLE `user_wechat` DROP COLUMN `profilePicture`;');
        $connection->exec('ALTER TABLE `user_wechat` DROP COLUMN `subscribeTime`;');
    }
}
