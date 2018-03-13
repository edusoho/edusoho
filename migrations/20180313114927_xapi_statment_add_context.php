<?php

use Phpmig\Migration\Migration;

class XapiStatmentAddContext extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `xapi_statement` ADD `context` TEXT NULL DEFAULT NULL COMMENT '上下文信息' AFTER `status`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            ALTER TABLE `xapi_statement` DROP `context`;
        ');
    }
}
