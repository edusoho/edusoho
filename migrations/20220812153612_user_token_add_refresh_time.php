<?php

use Phpmig\Migration\Migration;

class UserTokenAddRefreshTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER  TABLE `user_token` ADD COLUMN `refresh_token` varchar(255) COMMENT '刷新token' AFTER `remainedTimes`;
            ALTER  TABLE `user_token` ADD COLUMN `refresh_expire_time` BIGINT COMMENT '刷新token过期时间' AFTER `refresh_token`;
            ALTER  TABLE `user_token` ADD UNIQUE (`refresh_token`);
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
