<?php

use Phpmig\Migration\Migration;

class AddFieldAudioConvertStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if (!$this->isFieldExist('upload_files', 'audioConvertStatus')) {
            $db->exec("ALTER TABLE `upload_files` ADD `audioConvertStatus` enum('none', 'doing', 'success', 'error') NOT NULL DEFAULT 'none' COMMENT '视频转音频的状态';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if ($this->isFieldExist('upload_files', 'audioConvertStatus')) {
            $db->exec('ALTER TABLE `upload_files` DROP `audioConvertStatus`;');
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
