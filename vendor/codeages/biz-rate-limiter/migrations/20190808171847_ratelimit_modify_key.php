<?php

use Phpmig\Migration\Migration;

class RateLimitModifyKey extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container        = $this->getContainer();
        $connection = $container['db'];
        $connection->exec("
            ALTER TABLE `ratelimit` modify column `_key` varchar(128) NOT NULL
        ");

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container        = $this->getContainer();
        $connection = $container['db'];
        $connection->exec("ALTER TABLE `ratelimit` modify column `_key` varchar(64) NOT NULL");
    }
}
