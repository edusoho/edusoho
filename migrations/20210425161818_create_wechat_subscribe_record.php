<?php

use Phpmig\Migration\Migration;

class CreateWechatSubscribeRecord extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `wechat_subscribe_record` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `toId` varchar(64) NOT NULL DEFAULT '' COMMENT '用户openId',
              `templateCode` varchar(64) NOT NULL DEFAULT '' COMMENT '模板code',
              `templateType` varchar(32) NOT NULL DEFAULT '' COMMENT '模板类型（一次性、长期）',
              `isSend` tinyint NOT NULL DEFAULT 0 COMMENT '是否已发送',
              `createdTime` int unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='微信订阅记录表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE `wechat_subscribe_record`;');
    }
}
