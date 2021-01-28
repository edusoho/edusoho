<?php

use Phpmig\Migration\Migration;

class AddColumnActivityFinish extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if (!$this->isFieldExist('activity', 'finishType')) {
            $db->exec("ALTER TABLE `activity` ADD COLUMN `finishType`  varchar(64)  NOT NULL DEFAULT 'time' COMMENT '任务完成条件类型';");
        }

        if (!$this->isFieldExist('activity', 'finishData')) {
            $db->exec("ALTER TABLE `activity` ADD COLUMN `finishData`  varchar(256)  NOT NULL DEFAULT '0' COMMENT '任务完成条件数据';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if ($this->isFieldExist('activity', 'finishType')) {
            $db->exec('ALTER TABLE `activity` DROP `finishType`;');
        }

        if ($this->isFieldExist('activity', 'finishData')) {
            $db->exec('ALTER TABLE `activity` DROP `finishData`;');
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
