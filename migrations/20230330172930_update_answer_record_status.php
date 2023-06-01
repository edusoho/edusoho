<?php

use Phpmig\Migration\Migration;

class UpdateAnswerRecordStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            UPDATE `biz_answer_record` SET status='doing' WHERE status='paused';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
