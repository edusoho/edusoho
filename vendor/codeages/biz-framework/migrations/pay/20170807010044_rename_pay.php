<?php

use Phpmig\Migration\Migration;

class RenamePay extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        if ($this->isTableExist('user_cashflow')) {
            $db->exec(
                "RENAME TABLE user_cashflow TO biz_user_cashflow;"
            );
        }

        if ($this->isTableExist('user_balance')) {
            $db->exec(
                "RENAME TABLE user_balance TO biz_user_balance;"
            );
        }

        if ($this->isTableExist('payment_trade')) {
            $db->exec(
                "RENAME TABLE payment_trade TO biz_payment_trade;"
            );
        }

        if ($this->isTableExist('site_income')) {
            $db->exec(
                "RENAME TABLE site_income TO biz_site_income;"
            );
        }

        if ($this->isTableExist('pay_account')) {
            $db->exec(
                "RENAME TABLE pay_account TO biz_pay_account;"
            );
        }

        if ($this->isTableExist('security_answer')) {
            $db->exec(
                "RENAME TABLE security_answer TO biz_security_answer;"
            );
        }

        if ($this->isTableExist('site_cashflow')) {
            $db->exec(
                "RENAME TABLE site_cashflow TO biz_site_cashflow;"
            );
        }
    }

    protected function isTableExist($table)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $sql = "SHOW TABLES LIKE '{$table}'";
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
