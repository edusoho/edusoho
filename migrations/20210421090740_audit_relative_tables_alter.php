<?php

use Phpmig\Migration\Migration;

class AuditRelativeTablesAlter extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('');
    }
}
