<?php

use Phpmig\Migration\Migration;

class PayAccount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_pay_account` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` INT(10) unsigned NOT NULL COMMENT '所属用户',
              `password` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '密码',
              `salt` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
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
        $connection->exec("drop table `pay_account`;");
    }
}
