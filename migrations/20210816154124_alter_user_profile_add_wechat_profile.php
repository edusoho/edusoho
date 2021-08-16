<?php

use Phpmig\Migration\Migration;

class AlterUserProfileAddWechatProfile extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `user_profile` ADD COLUMN `wechat_nickname` varchar(512) default '' COMMENT '微信昵称' after `weixin`;
            ALTER TABLE `user_profile` ADD COLUMN `wechat_picture` varchar(256) default '' COMMENT '微信头像' after `wechat_nickname`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `user_profile` drop COLUMN `wechat_nickname`;
            ALTER TABLE `user_profile` drop COLUMN `wechat_picture`;
        ");
    }
}
