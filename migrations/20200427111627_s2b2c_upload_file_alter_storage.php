<?php

use Phpmig\Migration\Migration;

class S2b2cUploadFileAlterStorage extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("ALTER TABLE `upload_files` MODIFY COLUMN `storage` ENUM('local','cloud','supplier') NOT NULL COMMENT '文件存储方式';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
