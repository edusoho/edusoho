<?php

use Phpmig\Migration\Migration;

class BizUserCashflowAddTitle extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_user_cashflow', 'title')) {
            $connection->exec("ALTER TABLE `biz_user_cashflow` ADD COLUMN `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '流水名称'");
        }

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_user_cashflow` DROP COLUMN `title`;');
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
