<?php

use Phpmig\Migration\Migration;

class DeleteUnneedTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            DROP TABLE `biz_setting`;
            DROP TABLE `app`;
            DROP TABLE `biz_xapi_statement`;
        ');
    }
}
