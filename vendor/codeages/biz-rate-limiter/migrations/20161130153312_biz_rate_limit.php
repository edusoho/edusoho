<?php

use Phpmig\Migration\Migration;

class BizRateLimit extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container        = $this->getContainer();
        $connection = $container['db'];
        $connection->exec("
            CREATE TABLE `ratelimit` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `_key` varchar(64) NOT NULL,
              `data` varchar(32) NOT NULL,
              `deadline` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `_key` (`_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container        = $this->getContainer();
        $connection = $container['db'];
        $connection->exec("DROP TABLE `ratelimit`");
    }
}
