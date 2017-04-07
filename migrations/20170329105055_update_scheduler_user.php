<?php

use Phpmig\Migration\Migration;

class UpdateSchedulerUser extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("UPDATE user SET type = 'system' WHERE type = 'scheduler'");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
