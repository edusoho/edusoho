<?php

use Phpmig\Migration\Migration;

class UpdateUploadFilesTableConvertStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("
            ALTER TABLE `upload_files` CHANGE `convertStatus` `convertStatus`
              ENUM('none','waiting','doing','success','error','nonsupport','noneed','unknow') CHARACTER SET utf8
              NOT NULL DEFAULT 'none' COMMENT '文件转换状态';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec("
            ALTER TABLE `upload_files` CHANGE `convertStatus` `convertStatus`
              ENUM('none','waiting','doing','success','error') CHARACTER SET utf8
              NOT NULL DEFAULT 'none' COMMENT '文件转换状态';"
        );
    }

    public function getConnection()
    {
        $biz = $this->getContainer();

        return $biz['db'];
    }
}
