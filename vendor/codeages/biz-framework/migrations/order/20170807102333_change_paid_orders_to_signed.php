<?php

use Phpmig\Migration\Migration;

class ChangePaidOrdersToSigned extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec(" UPDATE `biz_order` set status = 'signed' WHERE status = 'paid'; ");

        $connection->exec(" UPDATE `biz_order_item` SET status = 'signed' WHERE status = 'paid'; ");

    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
