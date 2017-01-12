<?php

use Phpmig\Migration\Migration;

class VideoActivityChangeFinishCondition extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
             ALTER TABLE `video_activity` ADD COLUMN  `finshType` varchar(60) DEFAULT NULL COMMENT '完成类型';
             ALTER TABLE `video_activity` ADD COLUMN  `finishDetail` text COMMENT '完成条件';
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
             ALTER TABLE `video_activity` DROP COLUMN  `finshType`;
             ALTER TABLE `video_activity` DROP COLUMN  `finishDetail`;
        ");

    }
}
