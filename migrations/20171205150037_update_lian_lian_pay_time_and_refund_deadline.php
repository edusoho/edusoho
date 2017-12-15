<?php

use Phpmig\Migration\Migration;

class UpdateLianLianPayTimeAndRefundDeadline extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        // 以前的连连支付的 pay_time = 8位日期， 如 20171102, refund_deadline = pay_time + expired_refund_days * 86400
        $db->exec("update biz_order set refund_deadline = expired_refund_days * 86400 + updated_time, pay_time = updated_time where payment = 'lianlianpay' and length(pay_time) = 8;");
    }
}
