<?php

use Phpmig\Migration\Migration;

class RenamePayTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->renameTable('biz_payment_trade', 'biz_pay_trade');
        $this->renameTable('biz_user_cashflow', 'biz_pay_cashflow');
        $this->renameTable('biz_security_answer', 'biz_pay_security_answer');
        $this->renameTable('biz_user_balance', 'biz_pay_user_balance');
    }

    protected function renameTable($from, $to)
    {
        $biz = $this->getContainer();
        if ($this->isTableExist($from)) {
            $biz['db']->exec("
                ALTER TABLE `{$from}` RENAME TO `{$to}`;
            ");
        }
    }

    protected function isTableExist($table)
    {
        $biz = $this->getContainer();
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->renameTable('biz_pay_trade', 'biz_payment_trade');
        $this->renameTable('biz_pay_cashflow', 'biz_user_cashflow');
        $this->renameTable('biz_pay_security_answer', 'biz_security_answer');
        $this->renameTable('biz_pay_user_balance', 'biz_user_balance');
    }
}
