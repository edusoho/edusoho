<?php

use Phpmig\Migration\Migration;

class UserAddUuid extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `user` ADD COLUMN `uuid` varchar(255)  NOT NULL DEFAULT '' COMMENT '用户uuid';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `user` DROP COLUMN `uuid`;');
    }
}
