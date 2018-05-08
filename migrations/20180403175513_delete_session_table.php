<?php

use Phpmig\Migration\Migration;

class DeleteSessionTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('DROP TABLE  IF EXISTS `sessions`;');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        //do nothing
    }
}
