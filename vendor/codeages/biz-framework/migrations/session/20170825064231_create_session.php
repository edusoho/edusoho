<?php

use Phpmig\Migration\Migration;

class CreateSession extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_session` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `sess_id` varbinary(128) NOT NULL,
              `sess_user_id` int(10) unsigned NOT NULL DEFAULT '0',
              `sess_data` blob NOT NULL,
              `sess_time` int(10) unsigned NOT NULL,
              `created_time` int(10) unsigned NOT NULL,
              `sess_lifetime` mediumint(9) NOT NULL,
              `source` VARCHAR(32) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `sess_id` (`sess_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
