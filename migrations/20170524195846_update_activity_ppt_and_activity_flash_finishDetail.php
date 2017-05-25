<?php

use Phpmig\Migration\Migration;

class UpdateActivityPptAndActivityFlashFinishDetail extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("UPDATE `activity_ppt` SET `finishType`= 'end' WHERE finishType = '';");
        $db->exec("UPDATE `activity_flash` SET `finishType`= 'time', `finishDetail` = 1 WHERE finishType = '';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
