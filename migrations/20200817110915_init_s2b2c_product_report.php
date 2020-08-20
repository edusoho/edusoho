<?php

use Phpmig\Migration\Migration;

class InitS2b2cProductReport extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
CREATE TABLE `s2b2c_product_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL DEFAULT '0' COMMENT '供应商id',
  `productId` int(11) NOT NULL DEFAULT '0' COMMENT 's2b2c_product表的id',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '上报类型：join_course ｜ refund',
  `userId` int(11) NOT NULL DEFAULT '0' COMMENT '学员Id',
  `nickname` varchar(64) NOT NULL DEFAULT '' COMMENT '学员nickname',
  `orderId` int(11) NOT NULL DEFAULT '0' COMMENT '订单id',
  `status` varchar(64) NOT NULL DEFAULT 'created' COMMENT '上报状态：created | sent | succeed | failed',
  `reason` text COMMENT '错误原因',
  `createdTime` int(11) NOT NULL,
  `updatedTime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='商品结算上报表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec("DROP TABLE `s2b2c_product_settlement_report`");
    }
}
