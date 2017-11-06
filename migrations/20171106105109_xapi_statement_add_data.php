<?php

use Phpmig\Migration\Migration;

class XapiStatementAddData extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `xapi_statement` ADD COLUMN `data` text COMMENT '数据' after `status`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('ALTER TABLE `xapi_statement` DROP COLUMN `data`;');
    }
}
