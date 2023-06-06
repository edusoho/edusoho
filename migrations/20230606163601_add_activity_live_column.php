<?php

use Phpmig\Migration\Migration;

class addActivityLiveColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if ($this->isFieldExist('activity_live', 'replayStatus')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus` enum('ungenerated','generating','generated','videoGenerated','failure','none') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态' AFTER `liveProvider`;");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        if ($this->isFieldExist('activity_live', 'replayStatus')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus` enum('ungenerated','generating','generated','videoGenerated','failure') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态' AFTER `liveProvider`;");
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
