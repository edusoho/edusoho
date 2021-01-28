<?php

use Phpmig\Migration\Migration;

class AddIsDelete extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `message` add isDelete int(1) NOT NULL DEFAULT '0' COMMENT '是否已删除';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
