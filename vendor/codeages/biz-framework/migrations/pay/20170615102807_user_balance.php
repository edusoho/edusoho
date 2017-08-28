<?php

use Phpmig\Migration\Migration;

class UserBalance extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_user_balance` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(10) unsigned NOT NULL COMMENT '用户',
              `amount` int(10) NOT NULL DEFAULT '0' COMMENT '账户余额',
              `cash_amount` int(10) NOT NULL DEFAULT '0' COMMENT '现金余额',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("drop table `user_balance`;");
    }
}
