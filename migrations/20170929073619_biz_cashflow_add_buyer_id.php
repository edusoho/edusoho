<?php

use Phpmig\Migration\Migration;

class BizCashflowAddBuyerId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_user_cashflow', 'buyer_id')) {
            $connection->exec("ALTER TABLE `biz_user_cashflow` ADD COLUMN `buyer_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '买家'");
        }

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_user_cashflow` DROP COLUMN `buyer_id`;');
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
