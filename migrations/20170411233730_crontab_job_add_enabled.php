<?php

use Phpmig\Migration\Migration;

class CrontabJobAddEnabled extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        if (!$this->isFieldExist('crontab_job', 'enabled')) {
            $db->exec(
                "ALTER TABLE crontab_job Add COLUMN enabled tinyint(1) default 1 COMMENT '是否启用';"
            );

            $db->exec('
                UPDATE crontab_job SET `enabled` = 0;
            ');
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
