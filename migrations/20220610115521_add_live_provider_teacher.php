<?php

use Phpmig\Migration\Migration;

class AddLiveProviderTeacher extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
        create table if not exists `live_provider_teacher` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `userId` int(10) unsigned NOT NULL COMMENT '用户id',
            `provider` varchar(16) NOT NULL COMMENT '直播供应商',
            `providerTeacherId` int(10) unsigned NOT NULL COMMENT '直播供应商侧的主讲人id',
            `createdTime` int(10) unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `userId_provider` (`userId`, `provider`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `live_provider_teacher`;');
    }
}
