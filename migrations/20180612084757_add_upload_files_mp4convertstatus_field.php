<?php

use Phpmig\Migration\Migration;

class AddUploadFilesMp4ConvertStatusField extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if (!$this->isFieldExist('upload_files', 'mp4ConvertStatus')) {
            $db->exec("ALTER TABLE `upload_files` ADD `mp4ConvertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none' COMMENT '视频转mp4的状态';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if ($this->isFieldExist('upload_files', 'mp4ConvertStatus')) {
            $db->exec('ALTER TABLE `upload_files` DROP `mp4ConvertStatus`;');
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
