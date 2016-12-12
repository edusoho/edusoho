<?php

use Phpmig\Migration\Migration;

class OrdersUpdateData extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("UPDATE `orders` SET status='paid' WHERE id IN ( SELECT id FROM ( SELECT r.orderId id FROM `orders` o INNER JOIN `order_refund` r on o.id=r.orderId WHERE o.status='cancelled' and r.status='success') A)");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("UPDATE `orders` SET status='cancelled' WHERE id IN ( SELECT id FROM ( SELECT r.orderId id FROM `orders` o INNER JOIN `order_refund` r on o.id=r.orderId WHERE o.status='paid' and r.status='success') A)");
    }
}
