<?php

use Phpmig\Migration\Migration;

class AddOrderLog extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_order_log` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '订单id',
              `status` VARCHAR(32) NOT NULL COMMENT '订单状态',
              `user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户',
              `deal_data` TEXT COMMENT '处理数据',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
