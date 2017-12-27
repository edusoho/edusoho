<?php

use Phpmig\Migration\Migration;

class Checkin extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `user_sign` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `nickname` varchar(64) NOT NULL,
            `course_id` int(11) NOT NULL,
            `created_time` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
      ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `user_sign`');
    }
}
