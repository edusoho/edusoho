<?php

use Phpmig\Migration\Migration;

class XapiStatementPushingToCreated extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("UPDATE `xapi_statement` SET status ='created' WHERE status = 'pushing'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
