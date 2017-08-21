<?php

use Phpmig\Migration\Migration;

class AddOrderRefundItem extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
          CREATE TABLE `biz_order_item_refund` (
            `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
            `order_refund_id` INT(10) unsigned NOT NULL COMMENT '退款订单id',
            `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
            `order_item_id` INT(10) unsigned NOT NULL COMMENT '退款商品的id',
            `user_id` INT(10) unsigned NOT NULL COMMENT '退款人',
            `amount` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '涉及金额',
            `coin_amount` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '涉及虚拟币金额',
            `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
            `created_user_id` INT(10) unsigned NOT NULL COMMENT '申请者',
            `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
            `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        if (!$this->isFieldExist('biz_order_item_refund', 'coin_amount')) {
            $connection->exec(
                "ALTER TABLE `biz_order_item_refund` Add column `coin_amount` INT(10) unsigned NOT NULL COMMENT '涉及虚拟币金额';"
            );
        }

        if ($this->isFieldExist('biz_order_item_refund', 'currency')) {
            $connection->exec(
                "ALTER TABLE `biz_order_item_refund` drop column `currency`;"
            );
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
