<?php

use Phpmig\Migration\Migration;

class AddRetryNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `biz_job_fired` ADD COLUMN `retry_num` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '重试次数';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
