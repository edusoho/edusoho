<?php

use Phpmig\Migration\Migration;

class UploadFilesAddConvertMaxLevel extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `upload_files` ADD COLUMN `convertMaxLevel` VARCHAR(16) DEFAULT '' COMMENT '转码最高清晰度' AFTER `convertParams`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `upload_files` DROP COLUMN `convertMaxLevel`;');
    }
}
