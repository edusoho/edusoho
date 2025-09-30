<?php

use Phpmig\Migration\Migration;

class UploadFilesAddCategoryId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `upload_files` ADD COLUMN `categoryId` int(10) NOT NULL DEFAULT 0 COMMENT '分类ID'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `upload_files` DROP COLUMN `categoryId`');
    }
}
