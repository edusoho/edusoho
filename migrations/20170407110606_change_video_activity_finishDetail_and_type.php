<?php

use Phpmig\Migration\Migration;

class ChangeVideoActivityFinishDetailAndType extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `activity_video` modify column `finishDetail` varchar(32) NOT NULL DEFAULT '0' COMMENT '完成条件'");
        $db->exec("ALTER TABLE `activity_video` modify column `finishType` varchar(32) NOT NULL DEFAULT 'end' COMMENT '完成类型'");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
