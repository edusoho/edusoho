<?php

use Phpmig\Migration\Migration;

class DownloadActivityAddFileIds extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            DROP TABLE IF EXISTS `download_file`;
            ALTER TABLE  `download_activity` ADD  COLUMN    `fileIds` varchar(1024) DEFAULT NULL COMMENT '下载资料Ids';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
