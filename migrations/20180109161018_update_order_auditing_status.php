<?php

use Phpmig\Migration\Migration;

class UpdateOrderAuditingStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("
            update  `biz_order` set `status` = 'success' where id in (SELECT `order_id` FROM `biz_order_refund` WHERE `status` = 'auditing');
            update  `biz_order_item` set `status` = 'success' where order_id in (SELECT `order_id` FROM `biz_order_refund` WHERE `status` = 'auditing');
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
