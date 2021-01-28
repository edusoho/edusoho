<?php

use Phpmig\Migration\Migration;

class AddClearSessionJob extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("update biz_scheduler_job set class='Codeages\\\\Biz\\\\Framework\\\\Session\\\\Job\\\\SessionGcJob', name='SessionGcJob' where name='DeleteSessionJob';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
