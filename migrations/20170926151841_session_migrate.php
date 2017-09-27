<?php

use Phpmig\Migration\Migration;

class SessionMigrate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $currentTime = time();

        $connection->exec("
            INSERT INTO `biz_session` (
                sess_id, 
                sess_data,
                sess_time,
                sess_deadline,
                created_time
            ) select 
                sess_id, 
                sess_data,
                sess_time,
                sess_lifetime + sess_time,
                '{$currentTime}'
            from sessions;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
