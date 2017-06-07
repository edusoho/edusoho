<?php

use Phpmig\Migration\Migration;

class TestpaperItemResultAttachmentId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `testpaper_item_result_v8` add column  `attachmentId` int(10) DEFAULT '0' COMMENT '答题附件ID';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
