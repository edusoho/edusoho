<?php

use Phpmig\Migration\Migration;

class WechatSubscribeRecordTable extends Migration
{
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("CREATE TABLE `wechat_subscribe_record`  (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `toId` varchar(64) NOT NULL COMMENT '用户openId',
            `templateCode` varchar(64) NOT NULL COMMENT '模板code',
            `templateType` varchar(32) NOT NULL DEFAULT 'disposable' COMMENT '模板类型(disposable:一次性，longterm:长期)',
            `createdTime` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
            `updatedTime` int(10) UNSIGNED NOT NULL COMMENT '更新时间',
            PRIMARY KEY (`id`) USING BTREE
            ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;");
    }

    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `wechat_subscribe_record`;');
    }
}
