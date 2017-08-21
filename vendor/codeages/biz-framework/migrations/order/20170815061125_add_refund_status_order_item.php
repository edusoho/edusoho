<?php

use Phpmig\Migration\Migration;

class AddRefundStatusOrderItem extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_order_item', 'refund_status')) {
            $connection->exec(
                "ALTER TABLE `biz_order_item` Add column `refund_status` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '退款状态';"
            );
        }

        if (!$this->isFieldExist('biz_order_item', 'refund_id')) {
            $connection->exec(
                "ALTER TABLE `biz_order_item` Add column `refund_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '最新退款id';"
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
