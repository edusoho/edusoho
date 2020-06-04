<?php

use Phpmig\Migration\Migration;

class TableAddGoodsPurchaseVoucher extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
        CREATE TABLE `goods_purchase_voucher` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `specsId` int(11) unsigned NOT NULL,
          `goodsId` int(11) unsigned NOT NULL,
          `orderId` int(11) unsigned NOT NULL DEFAULT '0',
          `userId` int(11) unsigned NOT NULL,
          `effectiveType` varchar(64) NOT NULL DEFAULT '' COMMENT '生效类型',
          `effectiveTime` int(11) unsigned NOT NULL COMMENT '生效时间',
          `invalidTime` int(11) unsigned NOT NULL COMMENT '失效时间',
          `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
          `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
        DROP TABLE `goods_purchase_voucher`;    
        ');
    }
}
