<?php

use Phpmig\Migration\Migration;

class CrontabJobUpdateTargetType extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("UPDATE crontab_job SET targetType = 'task' WHERE targetType = 'lesson' AND name = 'SmsSendOneDayJob';");
        $db->exec("UPDATE crontab_job SET targetType = 'task' WHERE targetType = 'lesson' AND name = 'SmsSendOneHourJob';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("UPDATE crontab_job SET targetType = 'lesson' WHERE targetType = 'task' AND name = 'SmsSendOneDayJob';");
        $db->exec("UPDATE crontab_job SET targetType = 'lesson' WHERE targetType = 'task' AND name = 'SmsSendOneHourJob';");
    }
}
