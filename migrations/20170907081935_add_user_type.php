<?php

use Phpmig\Migration\Migration;

class AddUserType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_user_cashflow', 'user_type')) {
            $connection->exec(
                "ALTER TABLE `biz_user_cashflow` Add column `user_type` VARCHAR(32) NOT NULL COMMENT '用户类型：seller, buyer';"
            );
        }

        if (!$this->isFieldExist('biz_user_cashflow', 'amount_type')) {
            $connection->exec(
                "ALTER TABLE `biz_user_cashflow` Add column `amount_type` VARCHAR(32) NOT NULL COMMENT 'ammount的类型：coin, money';"
            );
        }
        if (!$this->isFieldExist('biz_user_cashflow', 'user_balance')) {
            $connection->exec(
                "ALTER TABLE `biz_user_cashflow` Add column `user_balance` int(10) NOT NULL DEFAULT '0' COMMENT '账单生成后的对应账户的余额，若amount_type为coin，对应的是虚拟币账户，amount_type为money，对应的是现金庄户余额'"
            );
        } else {
            $connection->exec(
                "ALTER TABLE `biz_user_cashflow` modify column `user_balance` int(10) NOT NULL DEFAULT '0' COMMENT '账单生成后的对应账户的余额，若amount_type为coin，对应的是虚拟币账户，amount_type为money，对应的是现金庄户余额'"
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
