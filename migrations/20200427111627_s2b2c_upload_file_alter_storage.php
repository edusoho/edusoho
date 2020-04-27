<?php

use Phpmig\Migration\Migration;

class S2b2cUploadFileAlterStorage extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "
            ALTER TABLE `upload_files` CHANGE `storage` `storage` ENUM('local','cloud','supplier') NOT NULL COMMENT '文件存储方式'
        ";

        $this->getContainer()->offsetGet('db')->exec($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
