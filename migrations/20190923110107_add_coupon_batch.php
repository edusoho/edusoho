<?php

use Phpmig\Migration\Migration;

class AddCouponBatch extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("
            CREATE TABLE IF NOT EXISTS `coupon_batch` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(64) NOT NULL COMMENT '批次名称',
              `token` varchar(64) NOT NULL DEFAULT '0',
              `type` enum('minus','discount') NOT NULL COMMENT '优惠方式',
              `generatedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '生成数',
              `usedNum` int(11) NOT NULL DEFAULT '0' COMMENT '使用次数',
              `rate` float(10,2) unsigned NOT NULL COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
              `prefix` varchar(64) NOT NULL COMMENT '批次前缀',
              `digits` int(20) unsigned NOT NULL COMMENT '优惠码位数',
              `money` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已优惠金额',
              `deadlineMode` enum('time','day') NOT NULL DEFAULT 'time' COMMENT '有效期模式',
              `deadline` int(10) unsigned NOT NULL COMMENT '失效时间',
              `fixedDay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '固定天数',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '使用对象类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0',
              `description` text COMMENT '优惠说明',
              `createdTime` int(10) unsigned NOT NULL,
              `fullDiscountPrice` float(10,2) unsigned DEFAULT NULL,
              `unreceivedNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '未领取的数量',
              `codeEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '通过优惠码渠道发放',
              `linkEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '通过链接渠道发放',
              `h5MpsEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '通过商品详情页小程序/微网校渠道发放',
              PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠码批次表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('DROP TABLE IF EXISTS `coupon_batch`;');
    }
}
