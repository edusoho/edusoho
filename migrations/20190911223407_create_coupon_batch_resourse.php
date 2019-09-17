<?php

use Phpmig\Migration\Migration;

class CreateCouponBatchResourse extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("
            CREATE TABLE IF NOT EXISTS `coupon_batch_resource` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `batchId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优惠劵批次Id',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '使用对象类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用对象Id',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='优惠码批次指定资源表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('
            DROP TABLE IF EXISTS `coupon_batch_resource`;
        ');
    }
}
