<?php

use Phpmig\Migration\Migration;

class ActivityLearnLogAddLearnTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE activity_learn_log ADD COLUMN learnedTime int(11) DEFAULT 0;
        ");

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE activity_learn_log DROP COLUMN learnedTime;
        ");
    }
}
