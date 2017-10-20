<?php

use Phpmig\Migration\Migration;

class BizOrderLogAddIp extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('biz_order_log', 'ip')) {
            $db->exec("ALTER TABLE `biz_order_log` ADD COLUMN `ip` VARCHAR(32) not null default '' COMMENT 'ip';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `biz_order_log` DROP COLUMN `ip`;");
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
