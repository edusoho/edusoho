<?php

use Phpmig\Migration\Migration;

class LiveActivityAddRoomCreated extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            UPDATE `live_activity` SET roomCreated = 1 WHERE liveId > 0;
        ');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
