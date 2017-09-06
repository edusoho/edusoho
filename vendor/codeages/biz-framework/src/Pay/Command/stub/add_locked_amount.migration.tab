<?php

use Phpmig\Migration\Migration;

class AddLockedAmount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_user_balance', 'locked_amount')) {
            $connection->exec(
                "ALTER TABLE `biz_user_balance` Add column `locked_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '冻结虚拟币金额';"
            );
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