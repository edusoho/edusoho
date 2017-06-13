<?php

use Phpmig\Migration\Migration;

class QuestionMarkerResultAddIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
          ALTER TABLE `question_marker_result` ADD INDEX `idx_qmid_taskid_stats` (`questionMarkerId`, `taskId`, `status`);
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
          ALTER TABLE `question_marker_result` DROP INDEX `idx_qmid_taskid_stats`
        ');
    }
}
