<?php

use Phpmig\Migration\Migration;

class NotificationCreateTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
