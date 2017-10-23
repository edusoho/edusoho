<?php

use Phpmig\Migration\Migration;

class BizPayCashflowAddTitle extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_pay_cashflow', 'title')) {
            $connection->exec("ALTER TABLE `biz_pay_cashflow` ADD COLUMN `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '流水名称'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_pay_cashflow` DROP COLUMN `title`;');
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
