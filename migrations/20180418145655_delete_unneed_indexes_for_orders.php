<?php

use Phpmig\Migration\Migration;

class DeleteUnneedIndexesForOrders extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            DROP INDEX `target_status` ON `orders`;
        ');
    }
}
