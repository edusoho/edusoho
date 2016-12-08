<?php

use Phpmig\Migration\Migration;

class DownloadActivityAddSummary extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
          ALTER TABLE `download_file` ADD COLUMN  `summary` TEXT COMMENT '文件描述';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
          ALTER TABLE `download_file` DROP  COLUMN `summary`;
        ");

    }
}
