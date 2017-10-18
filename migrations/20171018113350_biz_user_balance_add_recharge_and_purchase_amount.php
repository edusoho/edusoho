<?php

use Phpmig\Migration\Migration;

class BizUserBalanceAddRechargeAndPurchaseAmount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_user_balance', 'recharge_amount')) {
            $connection->exec("ALTER TABLE `biz_user_balance` ADD COLUMN `recharge_amount` int(10) NOT NULL DEFAULT '0' COMMENT '充值总额'");
        }

        if (!$this->isFieldExist('biz_user_balance', 'purchase_amount')) {
            $connection->exec("ALTER TABLE `biz_user_balance` ADD COLUMN `purchase_amount` int(10) NOT NULL DEFAULT '0' COMMENT '消费总额'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_user_balance` DROP COLUMN `recharge_amount`;');
        $connection->exec('ALTER TABLE `biz_user_balance` DROP COLUMN `purchase_amount`;');

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
