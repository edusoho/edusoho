<?php

use Phpmig\Migration\Migration;

class CashOrdersAddUnkey extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
            ALTER TABLE `cash_orders` ADD UNIQUE( `sn`); 
        ');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
