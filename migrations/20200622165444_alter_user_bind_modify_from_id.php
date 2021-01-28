<?php

use Phpmig\Migration\Migration;

class AlterUserBindModifyFromId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `user_bind` modify COLUMN `fromId` varchar(64) NOT NULL COMMENT '来源方用户ID';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `user_bind` modify COLUMN `fromId` varchar(32) NOT NULL COMMENT '来源方用户ID';");
    }
}
