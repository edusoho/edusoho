<?php

use Phpmig\Migration\Migration;

class TruncateQuestionMarkerResult extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
            TRUNCATE TABLE `question_marker_result`;
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
