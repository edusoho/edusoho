<?php

use Phpmig\Migration\Migration;

class UpdateAudioConvertStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE `upload_files` CHANGE `audioConvertStatus` `audioConvertStatus` ENUM('none','waiting','doing','success','error') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'none' COMMENT '视频转音频的状态';
            ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
