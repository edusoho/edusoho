<?php

use Phpmig\Migration\Migration;

class XapiStatementAddOccurTimne extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `xapi_statement` ADD COLUMN `occur_time` int(10) unsigned NOT NULL COMMENT '行为发生时间';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `xapi_statement` DROP COLUMN `occur_time`;");
    }
}
