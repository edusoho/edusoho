<?php

use Phpmig\Migration\Migration;

class ActivityChangeDesc extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz        = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE activity CHANGE `desc` remark TEXT DEFAULT NULL");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
