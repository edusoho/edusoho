<?php

use Phpmig\Migration\Migration;

class BizOrderItemAddOrderIdIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            ALTER TABLE `biz_order_item` ADD INDEX `order_id` (`order_id`);
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
