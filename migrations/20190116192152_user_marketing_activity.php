<?php

use Phpmig\Migration\Migration;

class UserMarketingActivity extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `user_marketing_activity` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                `mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号',
                `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动id',
                `joinedId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入id',
                `name` varchar(255) NOT NULL DEFAULT '' COMMENT '活动名称',
                `type` varchar(32) NOT NULL DEFAULT '' COMMENT '活动类型',
                `status` varchar(32) NOT NULL DEFAULT '' COMMENT '活动状态',
                `cover` varchar(255) NOT NULL DEFAULT '' COMMENT '活动图片',
                `itemType` varchar(32) NOT NULL DEFAULT '' COMMENT '商品类型',
                `itemSourceId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
                `originPrice` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '原价',
                `price` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '活动价',
                `joinedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入活动时间',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `joinedId_type` (`joinedId`,`type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户参与的营销活动表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('
            DROP TABLE IF EXISTS `user_marketing_activity`;
        ');
    }

    public function getConnection()
    {
        $biz = $this->getContainer();

        return $biz['db'];
    }
}
