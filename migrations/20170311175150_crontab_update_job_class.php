<?php

use Phpmig\Migration\Migration;

class CrontabUpdateJobClass extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('UPDATE crontab_job SET jobClass = \'Biz\\\\Order\\\\Job\\\\CancelOrderJob\' WHERE name = \'CancelOrderJob\';');
        $db->exec('UPDATE crontab_job SET jobClass = \'Biz\\\\User\\\\Job\\\\DeleteExpiredTokenJob\' WHERE name = \'DeleteExpiredTokenJob\';');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('UPDATE crontab_job SET jobClass = \'Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\' WHERE name = \'CancelOrderJob\';');
        $db->exec('UPDATE crontab_job SET jobClass = \'Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\' WHERE name = \'DeleteExpiredTokenJob\';');
    }
}
